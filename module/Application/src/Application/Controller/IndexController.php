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
use Application\Model\PdfHelper;
use Application\Model\PdfThumbnails;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ProgressBar\Upload\SessionProgress;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $logger = $this->getServiceLocator()->get('Zend\Log\Logger');
        $form = new UploadForm('upload-form');

        $request = $this->getRequest();
        if ($request->isPost()) {
            // Make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            
            $form->setData($post);
            
            if ($form->isValid()) {
                $data = $form->getData();
                // Form is valid, save the form!
                if (!empty($post['isAjax'])) {
                    $tmpFile = $form->getInputFilter()->getValue('pdf-file');
                    $thumb = new PdfThumbnails($tmpFile['tmp_name']);
                    $logger->info($thumb->convertToImage());
                    
                    return new JsonModel(array(
                        'status'   => true,
//                        'redirect' => $this->url()->fromRoute('upload-form/success'),
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
                         'status'     => false,
                         'formErrors' => $form->getMessages(),
                         'formData'   => $form->getData(),
                     ));
                }
            }
        }

        return array('form' => $form);
    }
    
    public function uploadProgressAction(){
        $logger = $this->getServiceLocator()->get('Zend\Log\Logger');
        $id = $this->params()->fromQuery('id', null);
        $progress = new SessionProgress();
        $response = new JsonModel($progress->getProgress($id));
        return $response;
    }
    
    public function uploadSuccessAction(){
        $logger = $this->getServiceLocator()->get('Zend\Log\Logger');
        $logger->info('success');
    }
    
    public function changeTextAction(){
        $logger = $this->getServiceLocator()->get('Zend\Log\Logger');
        $logger->info(__LINE__.'change text');
        
        $request = $this->getRequest();
        $filePath = '/var/www/zf/data/fileupload/robust_562be5d4d7390.pdf';
        $logger->info($filePath);
        $pdf = new PdfHelper($filePath);
        $logger->info('xxxx'.print_r($pdf->test(), true));
    }
    
}
