<?php
    namespace GcFrontend\Controller;
    use Zend\Mvc\Controller\AbstractActionController;
    use DOMPDFModule\View\Model\PdfModel;
    use DOMPDFModule\View\Renderer\PdfRenderer;
    use DOMPDF;
      
    class PdfController extends AbstractActionController {
      
        public function generate_pdf_invoice($content, $pdf_id=null, $is_bank_details=false) {

            $dompdf = new DOMPDF();
            if ($is_bank_details) {
                $dompdf->set_paper(array(0, 0, 650, 500));  
            }else{
                $dompdf->set_paper(array(0, 0, 650, 450));  
            }
                      
            $dompdf->load_html($content);            
            $dompdf->render();
            $invoice_pdf = $dompdf->output();
            
            if ($pdf_id == null) {
                $pdf_id = rand(0,1000);   
            }      
            $pdf_invoice_file = $_SERVER['DOCUMENT_ROOT'].'/public/media/pdf/invoice/invoice-'.$pdf_id.'.pdf';           
            file_put_contents($pdf_invoice_file,$invoice_pdf);             
            return $pdf_invoice_file;
        }        
    }

