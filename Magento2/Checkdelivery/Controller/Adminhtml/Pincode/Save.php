<?php
namespace Bluethink\Checkdelivery\Controller\Adminhtml\Pincode;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
class Save extends \Magento\Backend\App\Action
{

    protected $_storeManager;
    public $manager;
    public function __construct(
    \Magento\Backend\App\Action\Context $context,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        $this->_storeManager;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        parent::__construct($context);
     
    }
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
	public function execute()
    {
		
        $data = $this->getRequest()->getParams();
        if ($data) {
            $model = $this->_objectManager->create('Bluethink\Checkdelivery\Model\Pincode');
            if(isset($_FILES['csv_file']['name']) && $_FILES['csv_file']['name'] != '')
            {
                $csv_extension=explode('.', $_FILES['csv_file']['name']);
                if ($csv_extension[1]!='csv') {
                   $this->messageManager->addError('Only CSV file allowed..');
                   return $this->_redirect('*/*/new');
                }
                else
                {
                    $result = $this->_uploadCsvFile($_FILES['csv_file']);
                    $path = $result['path'].$result['file'];
                    $handle=fopen($path, 'r');
                    $row=0;
                    while (($csv = fgetcsv($handle)) !== FALSE)
                    {
                        // echo "<pre>"; print_r($csv); echo "</pre>";
                        // exit;
                        if($row>0)
                        {
                            $model = $this->_objectManager->create('Bluethink\Checkdelivery\Model\Pincode');
                            //echo "<pre>"; print_r($csv); echo "</pre>";
                            $model->setPincode($csv[0]);
                            $model->save();
                        }
                        $row++;

                    }
                    // exit();
                }
            }
			$id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }
			
            $model->setData($data);
			
            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Frist Grid Has been Saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId(), '_current' => true));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the banner.'));
            }

            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/edit', array('banner_id' => $this->getRequest()->getParam('banner_id')));
            return;
        }
        $this->_redirect('*/*/');
    }

    public function _uploadCsvFile($_csvfile){
        $uploader = $this->_fileUploaderFactory->create(array('fileId' => 'csv_file'));
        $uploader->setAllowedExtensions(array('csv'));
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(true);
        $mediaDirectory = $this->_objectManager
                        ->get('Magento\Framework\Filesystem')
                        ->getDirectoryRead(DirectoryList::MEDIA);

        $_result = $uploader->save($mediaDirectory->getAbsolutePath('/pincsv'));
        return $_result;
    }
}
