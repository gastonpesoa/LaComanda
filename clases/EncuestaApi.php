<?php
namespace Clases;
use App\Models\Encuesta;
use App\Models\Pedido;

class EncuestaApi
{
    public function __construct()
    {
    }

    public function InsertEncuesta($request, $response)
    {
        $data = $request->getParsedBody();
        $fecha = date('Y-m-d H:i:s');
        $pedidoORM = new Pedido();
        $pedido = $pedidoORM->where('idMesa', '=', $data['codigoMesa'])->first();
        $idMozo = $pedido->idMozo;

        $encuesta = new Encuesta();
        $encuesta->codigoMesa = $data['codigoMesa'];
        $encuesta->idMozo = $idMozo;
        $encuesta->puntajeMesa = $data['puntajeMesa'];
        $encuesta->puntajeMozo = $data['puntajeMozo'];
        $encuesta->puntajeCocinero = $data['puntajeCocinero'];
        $encuesta->puntajeRestaurante = $data['puntajeRestaurante'];
        $encuesta->comentario = $data['comentario'];
        $encuesta->fecha = $fecha;
        $encuesta->save();
        $respuesta = array("Estado" => "OK", "Mensaje" => "Encuesta realizada");
        return $response->withJson($respuesta, 200);
    }
}