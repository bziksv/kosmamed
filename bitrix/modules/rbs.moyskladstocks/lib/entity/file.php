<?php
namespace Rbs\MoyskladStocks\Entity;

use \Rbs\MoyskladStocks\ApiNew;
use \Rbs\MoyskladStocks\Config;
use \Rbs\MoyskladStocks\LangMsg;
use \Rbs\MoyskladStocks\Utils;
use \Bitrix\Main\Application;

/*
TRUNCATE table b_file;
TRUNCATE table b_file_duplicate;
TRUNCATE table b_file_hash;
*/

class File
{    
    private $isLoadedFile = false;
    private $fileObject = null;
    private $fileParams = null;
    private $lastError = '';

    function __construct($fileObject = null)
    {
        if(is_object($fileObject) && property_exists($fileObject, 'meta')){
            try {
                
                $this->fileObject = $fileObject;
                $this->fileParams = (object)[
                    'orig_name' => $this->fileObject->filename,
                    'ms_updated' => $this->fileObject->updated,
                    'size' => $this->fileObject->size,
                    'name' => '',
                    'hash' => '',
                    'ms_id' => '',
                    'upload_dir' => '',
                    'dir' => '',
                    'bx_id' => '',
                    'bx_upload_dir' => '/' . \COption::GetOptionString("main", "upload_dir", "upload") . '/',
                    'bx_size' => '',
                    'handler_id' => Config::getModuleId(true),
                    'module_id' => 'iblock',
                    //'profile_id' => Config::getProfileId(),
                    'deleted_now' => false
                ];

                $this->fillFileParams();

                if($this->isExsistBxFile()){
                    if(Config::checkFeature('image_clear_size') && !$this->checkSize()){
                        $this->deleteRealFile();
                        $this->deleteBxFile();
                    }
                }

                if ($this->isDeletedNow() || !$this->isExsistBxFile()) {
                    $this->loadRealFile();
                    $this->loadBxFile();
                }

                if ($this->getBxId() > 0) {
                    $this->deleteRealFile();
                    $this->isLoadedFile = true;
                }
                
            } catch (\Throwable $e) {    
                $this->isLoadedFile = false;
                $this->lastError = Utils::build_exception_message($e);    
            }
        }
    }

    public function isLoaded()
    {
        return $this->isLoadedFile;
    }

    public function getMsUploadFilePath()
    {
        return $this->fileParams->upload_dir . $this->fileParams->name;
    }

    public function getMsFilePath()
    {
        return $this->fileParams->dir . $this->fileParams->name;
    }

    public function getMsFileObject()
    {
        return $this->fileObject;
    }

    public function getExtId()
    {
        return $this->fileParams->hash;
    }

    public function getOrigName()
    {
        return $this->fileParams->orig_name;
    }

    public function getBxId()
    {
        return $this->fileParams->bx_id;
    }

    public function getMsId()
    {
        return $this->fileParams->ms_id;
    }

    public function getMsUpdated()
    {
        return $this->fileParams->ms_updated;
    }

    public function getSize()
    {
        return $this->fileParams->size;
    }

    public function checkSize()
    {
        return (int)$this->fileParams->bx_id > 0 && (float)$this->fileParams->size === (float)$this->fileParams->bx_size;
    }

    public function isDeletedNow()
    {
        return $this->fileParams->deleted_now;
    }

    public function getBxFileArray()
    {
        $fileArray = \CFile::MakeFileArray($this->getMsUploadFilePath());
        $fileArray['MODULE_ID'] = $this->fileParams->module_id;
        $fileArray['name'] = $this->fileParams->orig_name;
        $fileArray['external_id'] = $this->fileParams->hash;
        return $fileArray;
    }

    public function getBxFileArrayFromFileId()
    {
        $fileArray = \CFile::MakeFileArray($this->getBxId());
        $fileArray['MODULE_ID'] = $this->fileParams->module_id;
        $fileArray['name'] = $this->fileParams->orig_name;
        $fileArray['external_id'] = $this->fileParams->hash;
        return $fileArray;
    }

    private function fillFileParams()
    {
        $this->buildName();
        $this->buildFileMsId();
        $this->buildHash();        
        $this->buildFileUploadDir();
    }

        private function buildName()
        {
            $fileInfo = \pathinfo($this->fileParams->orig_name);
            if($fileInfo['extension'] === 'jfif'){
                $fileInfo['extension'] = 'jpeg';
            }

            $this->fileParams->orig_name = $fileInfo['filename'] . '.' . $fileInfo['extension'];
            $fileName = md5($fileInfo['filename']) . '.' . $fileInfo['extension'];

            if(empty($fileName)){
                throw new \Bitrix\Main\SystemException(LangMsg::get('FILE_FILE_NAME_EMPTY'));
            }            

            $this->fileParams->name = $fileName;
        }

        private function buildHash()
        {
            $hash = md5(implode('|', [
                $this->getMsId(),
                $this->getOrigName(),
                $this->getMsUpdated(),
                $this->getSize(),
            ]));
            if(empty($hash)){
                throw new \Bitrix\Main\SystemException(LangMsg::get('FILE_FILE_HASH_EMPTY'));
            }
            $this->fileParams->hash = $hash;
        }

        private function buildFileMsId()
        {
            $fileMsId = !empty($this->fileObject->meta->downloadHref) ? array_pop(explode('/download/', $this->fileObject->meta->downloadHref)) : '';
            if(empty($fileMsId)){
                throw new \Bitrix\Main\SystemException(LangMsg::get('FILE_FILE_MS_ID_EMPTY')); 
            }
            $this->fileParams->ms_id = $fileMsId;
        }

        private function buildFileUploadDir()
        {
            $uploadDir = Config::getUploadDir("files/{$this->fileParams->ms_id}/{$this->fileParams->hash}");;
            if(!is_dir($uploadDir)){
                throw new \Bitrix\Main\SystemException(LangMsg::get('FILE_UPLOAD_DIR_CREATE_ERROR'));
            }
            $this->fileParams->upload_dir = $uploadDir;
            $this->fileParams->dir = str_replace(Application::getDocumentRoot() . $this->fileParams->bx_upload_dir, '', $uploadDir);
        }

    private function loadRealFile()
    {
        if(!\file_exists($this->getMsUploadFilePath())){
            if(!ApiNew::saveFile($this->getMsUploadFilePath(), $this->getMsFileObject())){
                throw new \Bitrix\Main\SystemException(LangMsg::get('FILE_DOWNLOAD_ERROR'));
            }
        }
    }

    private function deleteRealFile()
    {
        if (\file_exists($this->getMsUploadFilePath())) {
            unlink($this->getMsUploadFilePath());
        }
    }

    private function deleteBxFile()
    {
        if(!empty($this->fileParams->hash)){
            if($fileOb = \CFile::GetList(['ID' => 'DESC'], ['EXTERNAL_ID' => $this->fileParams->hash, 'HANDLER_ID' => $this->fileParams->handler_id])->GetNext()){
                \CFile::Delete((int)$fileOb['ID']);
                $this->fileParams->bx_id = '';
                $this->fileParams->bx_size = '';
                $this->fileParams->deleted_now = true;
            }
        }        
    }

    private function isExsistBxFile()
    {
        if($fileOb = \CFile::GetList(['ID' => 'DESC'], ['EXTERNAL_ID' => $this->fileParams->hash, 'HANDLER_ID' => $this->fileParams->handler_id])->GetNext()){
            if(\file_exists(Application::getDocumentRoot() . $this->fileParams->bx_upload_dir . $fileOb['SUBDIR'] . '/' . $fileOb['FILE_NAME'])){
                $this->fileParams->bx_id = $fileOb['ID'];
                $this->fileParams->bx_size = $fileOb['FILE_SIZE'];
                return true;
            }         
        }
        return false;
    }

    private function loadBxFile()
    {   
        if($this->getBxId() <= 0){
            $fileArray = $this->getBxFileArray();
            $fileArray['HANDLER_ID'] = $this->fileParams->handler_id;
            $fileId = \CFile::SaveFile($fileArray, $this->fileParams->dir);
        }
        
        if($fileId <= 0){
            throw new \Bitrix\Main\SystemException(LangMsg::get('FILE_CREATE_BX_FILE_ERROR'));
        }
        $this->fileParams->bx_id = $fileId;

        return $this->getBxId() > 0;
    }
}