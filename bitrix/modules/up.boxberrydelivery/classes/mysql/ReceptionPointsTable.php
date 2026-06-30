<?php

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Result;
use Bitrix\Main\Text\Encoding;

Loc::loadMessages(__FILE__);

class ReceptionPointsTable extends DataManager
{
    public static $addingReceptionPoints = false;

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_boxberry_reception_points';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary(true)
                ->configureAutocomplete(true)
                ->configureTitle(Loc::getMessage('RECEPTION_POINTS_ENTITY_ID_FIELD')),

            (new StringField('CODE'))
                ->configureTitle(Loc::getMessage('PRECEPTION_POINTS_ENTITY_CODE_FIELD'))
                ->addValidator(new LengthValidator(null, 255)),

            (new StringField('NAME'))
                ->configureTitle(Loc::getMessage('RECEPTION_POINTS_ENTITY_NAME_FIELD'))
                ->addValidator(new LengthValidator(null, 255)),

            (new StringField('CITY'))
                ->configureTitle(Loc::getMessage('RECEPTION_POINTS_ENTITY_CITY_FIELD'))
                ->addValidator(new LengthValidator(null, 255)),

        ];
    }

    /**
     * @throws \Exception
     */
    public static function addReceptionPoints(array $points): void
    {
        self::$addingReceptionPoints = true;

        if (empty($points)) {
            throw new \Exception(Loc::getMessage('BB_EMPTY_RECEPTION_POINTS_ARRAY'));
        }

        if (self::getCount() > 0) {
            self::truncate();
        }

        if (method_exists(self::class, 'addMulti')) {
            $batchSize = 400;
            $chunks = array_chunk($points, $batchSize);

            foreach ($chunks as $chunk) {
                $result = self::addMulti($chunk, true);
                if (!$result->isSuccess()) {
                    throw new \Exception(implode(', ', $result->getErrorMessages()));
                }
            }
        } else {
            foreach ($points as $point) {
                $result = self::add($point);
                if (!$result->isSuccess()) {
                    throw new \Exception(implode(', ', $result->getErrorMessages()));
                }
            }
        }

        self::$addingReceptionPoints = false;
    }

    public static function isEmpty(): bool
    {
        return self::getCount() == 0;
    }

    private static function truncate(): void
    {
        $connection = \Bitrix\Main\Application::getConnection();
        $connection->truncateTable(self::getTableName());
    }

    public static function getReceptionPoints($searchTerm): Result
    {
        $result = new Result();
        if (self::$addingReceptionPoints) {
            $result->addError(new Error(Loc::getMessage('BB_RECEPTION_POINT_ADD_IN_PROCESS')));
            return $result;
        }

        try {
            $query = self::query()
                ->addSelect('NAME')
                ->whereLike('NAME', '%' . Encoding::convertEncodingToCurrent($searchTerm) . '%')
                ->setLimit(10)
                ->exec()
                ->fetchAll();

            if (!$query) {
                $result->addError(new Error(Loc::getMessage('BB_RECEPTION_POINT_IN_SEARCH_NOT_FOUND') . $searchTerm));
                return $result;
            }
        } catch (\Exception $e) {
            $result->addError(new Error($e->getMessage()));
            return $result;
        }

        $result->setData(array_column($query, 'NAME'));

        return $result;
    }

    public static function getPointCodeByName($name): string
    {
        try {
            $query = self::query()
                ->addSelect('CODE')
                ->where('NAME', Encoding::convertEncodingToCurrent($name))
                ->setLimit(1)
                ->setCacheTtl(3600)
                ->exec()
                ->fetch();
        } catch (\Exception $e) {
            return '';
        }

        return $query['CODE'] ?? '';
    }

    public static function getPointNameByCode($code): string
    {
        try {
            $query = self::query()
                ->addSelect('NAME')
                ->where('CODE', Encoding::convertEncodingToCurrent($code))
                ->setLimit(1)
                ->setCacheTtl(3600)
                ->exec()
                ->fetch();
        } catch (\Exception $e) {
            return '';
        }

        return $query['NAME'] ?? '';
    }

    public static function getReceptionPointsSource()
    {
        $result = '';

        if (self::isEmpty()) {
            return $result;
        }

        $query = self::query()
            ->addSelect('CODE')
            ->setLimit(1)
            ->exec()
            ->fetch();

        if (!$query) {
            return $result;
        }

        $result = $query['CODE'];

        if (CBoxberry::isPointCodeYaDelivery($result)) {
            return CBoxberry::SOURCE_YANDEX;
        } else {
            return CBoxberry::SOURCE_BOXBERRY;
        }

    }


}