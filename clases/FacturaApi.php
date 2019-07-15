<?php
namespace Clases;
use App\Models\Factura;

class FacturaApi
{
    public function __construct()
    {
    }

    public static function Facturar($importe, $codigoMesa)
    {
        $fecha = date('Y-m-d H:i:s');
        $factura = new Factura();
        $factura->importe = $importe;
        $factura->codigoMesa = $codigoMesa;
        $factura->fecha = $fecha;
        $factura->save();
        return array("Estado" => "OK", "Mensaje" => "Factura realizada");
    }

    public function ListarPDF($request, $response)
    {
        $pdf = new \FPDF("P","mm","A4");
        $pdf->AddPage();
        $pdf->SetFont("Arial","B",12);
        $pdf->Cell(50,10,'Fecha',1,0,"C");
        $pdf->Cell(50,10,'Codigo Mesa',1,0,"C");
        $pdf->Cell(50,10,'Importe',1,1,"C");

        $facturaORM = new Factura();
        $facturas = $facturaORM::all();
        foreach($facturas as $factura){
            $pdf->Cell(50,10,$factura->fecha,1,0,"C");
            $pdf->Cell(50,10,$factura->codigoMesa,1,0,"C");
            $pdf->Cell(50,10,"$".$factura->importe,1,1,"C");
        }

        $pdf->Output("F","./../reportes/Ventas.pdf",true);
        $respuesta = array("Estado" => "OK", "Mensaje" => "PDF generado correctamente.");
        return $response->withJson($respuesta, 200);
    }

    public function ListarExcel($request, $response){
        $excel = new \PHPExcel();
        $excel->getProperties()
        ->setCreator("Gaston Pesoa")
        ->setTitle("Listado de Ventas")
        ->setDescription("Listado de Ventas");

        $excel->setActiveSheetIndex(0);
        $excel->getActiveSheet()
        ->getColumnDimension('A')
        ->setAutoSize(true);

        $excel->getActiveSheet()
        ->getColumnDimension('B')
        ->setAutoSize(true);

        $excel->getActiveSheet()
        ->getColumnDimension('C')
        ->setAutoSize(true);

        $excel->getActiveSheet()->setTitle("Listado de Ventas");

        $excel->getActiveSheet()->setCellValue("A1","Fecha");
        $excel->getActiveSheet()->setCellValue("B1","Codigo Mesa");
        $excel->getActiveSheet()->setCellValue("C1","Importe");

        $facturaORM = new Factura();
        $facturas = $facturaORM::all();
        $fila = 2;
        foreach($facturas as $factura){
            $excel->getActiveSheet()->setCellValue("A$fila",$factura->fecha);
            $excel->getActiveSheet()->setCellValue("B$fila",$factura->codigoMesa);
            $excel->getActiveSheet()->setCellValue("C$fila",$factura->importe);
            $fila++;
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename-"./../reportes/Ventas.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = \PHPExcel_IOFactory::createWriter($excel,"Excel2007");
        $writer->save("./../reportes/Ventas.xlsx");
        $respuesta = array("Estado" => "OK", "Mensaje" => "Excel generado correctamente.");
        return $response->withJson($respuesta, 200);
    }
}