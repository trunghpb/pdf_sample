<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Form\UploadForm;
use Application\Model\PdflibHelper;
use Application\Model\PdfThumbnails;
use Application\Model\PdftkHelper;
use Zend\Log\Logger;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\ProgressBar\Upload\SessionProgress;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController {

    /**
     * @var Logger
     */
    protected $logger;
    protected $acceptCriteria = [
        'Zend\View\Model\JsonModel' => [
            'application/json',
        ],
        'Zend\View\Model\ViewModel' => [
            'text/html',
        ],
    ];

//    public function uploadImageAction(){
//        $form = new UploadForm('upload-form');
//    }


    public function indexAction() {
        $logger = $this->getServiceLocator()->get('Zend\Log\Logger');
        $form = new UploadForm('upload-form');

        $request = $this->getRequest();
        if ($request->isPost()) {
            // Make certain to merge the files info!
            $post = array_merge_recursive(
                    $request->getPost()->toArray(), $request->getFiles()->toArray()
            );

            $form->setData($post);

            if ($form->isValid()) {
                $data = $form->getData();
                // Form is valid, save the form!
                if (!empty($post['isAjax'])) {
                    $tmpFile = $form->getInputFilter()->getValue('pdf-file');

                    return new JsonModel(array(
                        'status' => true,
                        'formData' => $data,
                    ));
                } else {
                    // Fallback for non-JS clients
                    return $this->redirect()->toRoute('upload-form/success');
                }
            } else {

                if (!empty($post['isAjax'])) {
                    // Send back failure information via JSON
                    return new JsonModel(array(
                        'status' => false,
                        'formErrors' => $form->getMessages(),
                        'formData' => $form->getData(),
                    ));
                }
            }
        }

        return array('form' => $form);
    }

    public function uploadProgressAction() {
        $logger = $this->getServiceLocator()->get('Zend\Log\Logger');
        $id = $this->params()->fromQuery('id', null);
        $progress = new SessionProgress();
        $response = new JsonModel($progress->getProgress($id));
        return $response;
    }

    public function uploadSuccessAction() {
        $logger = $this->getServiceLocator()->get('Zend\Log\Logger');
        $logger->info('success');
    }

    public function editPdfFileAction() {
        $this->logger->info(__LINE__ . ' ' . __METHOD__);

        $request = $this->getRequest()->getPost()->toArray();
        $this->logger->info($this->params()->fromQuery());
        $param = $this->params()->fromQuery();
        $filename = $this->params()->fromQuery('filename', null);
        $fontname = $this->params()->fromQuery('fontname', null);
        $imageName = $this->params()->fromQuery('image-name', null);
        $imageItem = $this->params()->fromQuery('image-item', null);

        // validate  
        unset($param['filename']);
        $validateMessage = '';
        $result = true;
        $limit = 100;
        foreach ($param as $key => $value) {
            if (mb_strlen($value, 'UTF-8') > $limit) {
                $result = false;
                $validateMessage .= "$key is longer than $limit character<br/>";
            }
        }

        if (!$result) {
            return new JsonModel([
                'status' => false,
                'message' => $validateMessage,
            ]);
        }
        $imageData = [];
        if ($imageItem && $imageName) {
            $imageData[$imageItem] = $imageName;
            unset($param[$imageItem]);
        }

        $result = PdflibHelper::create($filename)
                        ->setFont($fontname)->updateContent($param, $imageData);

        $thumb = new PdfThumbnails($filename);
        $this->logger->info($thumb->convertToImage());

        return new JsonModel([
            'status' => $result,
            'message' => PdftkHelper::create()->getError(),
        ]);
    }

    public function listPdfFileAction() {
        $logger = $this->getServiceLocator()->get('Zend\Log\Logger');
        $logger->info(__LINE__ . 'list pdf file');
        $pdfList = PdftkHelper::create()->getPdfFiles();
        $logger->info(print_r($pdfList, true));

        return new JsonModel([
            'status' => true,
            'aryFiles' => $pdfList
        ]);
    }

    public function showEditPdfOverlayAction() {
        $this->logger->info(__LINE__ . ' ' . __METHOD__);

        $dataViewModel = new ViewModel();
        $dataViewModel->setTemplate("application/index/edit-pdf-overlay.phtml");

        $viewModel = $this->acceptableViewModelSelector($this->acceptCriteria);
        $viewRenderer = $this->getServiceLocator()->get('ViewRenderer');

        $fileNameParam = $this->params()->fromQuery('filename', null);
        $dataViewModel->fieldList = PdflibHelper::create($fileNameParam)->getFields();
        $dataViewModel->filename = $fileNameParam;
        $this->logger->info($dataViewModel->fieldList);
        $viewModel->html = $viewRenderer->render($dataViewModel);
        return $viewModel;
    }

    public function onDispatch(MvcEvent $e) {
        $this->logger = $this->getServiceLocator()->get('Zend\Log\Logger');
        parent::onDispatch($e);
    }

    public function downloadPdfFileAction() {
        $this->logger->info(__LINE__ . ' ' . __METHOD__);
        $file = $this->params()->fromQuery('filename', null);
        ;
        $fileName = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/data/filled/' . $file . '.filled.pdf';
        if (!is_file($fileName)) {
            //do something
        }

        $fileContents = file_get_contents($fileName);

        $response = $this->getResponse();
        $response->setContent($fileContents);

        $headers = $response->getHeaders();
        $headers->clearHeaders()
                ->addHeaderLine('Content-Type', 'application/octet-stream')
                ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $file . '"')
                ->addHeaderLine('Content-Length', strlen($fileContents))
                ->addHeaderLine('Cache-Control', 'must-revalidate')
                ->addHeaderLine('Pragma', 'public')
        ;

        $this->logger->info(__LINE__ . ' ' . __METHOD__);
        return $this->response;
    }

}
