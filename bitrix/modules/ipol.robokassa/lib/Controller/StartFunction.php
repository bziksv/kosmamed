<?php

    namespace Ipol\Robokassa\Controller;


    use Bitrix\Main;
    use Ipol\Robokassa;

    /**
     * Class StartFunction
     * @package Ipol\Robokassa\Controller
     */
    final class StartFunction extends Main\Engine\Controller
    {

        public function __construct(Main\Request $request = null)
        {

            parent::__construct($request);

            try
            {
                Main\Loader::includeModule('ipol.robokassa');
            }
            catch (Main\LoaderException $e)
            {
                $this->errorCollection[] = new Main\Error($e->getMessage());
                return;
            }

            if($this->getRequest()->getRequestMethod() !== 'POST')
            {
                $this->errorCollection[] = new Main\Error('only post allowed');
                return;
            }
        }

    }