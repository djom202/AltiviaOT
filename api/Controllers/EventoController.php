<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EventoController
 *
 * @author edinson
 */
class EventoController extends ControllerBase {
    //put your code here
    function get () {
        $params = Partial::prefix($this->get, ':');
        
        $result = $this->getModel('movimientos')->select(
            $params
        );
        
        $response = Partial::arrayNames($result);
        $tiempos = $this->getModel('tiempos');
        $remolcadores = $this->getModel('remolcados');
        
        for($i=0; $i<count($response); $i++) {
            $tiempo = $tiempos->select(array (
                ':idevento' => $response[$i]['idevento']
            ));

            $remolcador = Partial::arrayNames($remolcadores->select(array (
                ':idevento' => $response[$i]['idevento']
            )), array('idevento'));
            
            $response[$i]['tiempos'] = Partial::arrayNames($tiempo);
            $response[$i]['remolcadores'] = Partial::arrayNames($remolcador);
        }
        
        HTTP::JSON(Partial::createResponse(HTTP::Value(200), $response));
    }

    function getTimes () {
        $_filled = Partial::_filled($this->post, array('idevento'));

        if($_filled) {
            $tiempos = $this->getModel('tiempos')->select(array(
                ':idevento' => $this->post['idevento']
            ));

            $response = Partial::arrayNames($tiempos);        
            HTTP::JSON(Partial::createResponse(HTTP::Value(200), $response));
        }

        HTTP::JSON(400);
    }
    
    function detailed () {
        $params = Partial::prefix($this->get, ':');
        
        $result = $this->getModel('evento')->select(
            $params
        );
        
        $response = Partial::arrayNames($result);
        
        HTTP::JSON(Partial::createResponse(HTTP::Value(200), $response[0]));
    }
    
    function add () {
        $_filled = Partial::_filled($this->post, array('idproceso', 'idterminal', 'idestado_', 'idpiloto', 'fecha', 'hora'));
        // $_empty = Partial::_empty($this->post, array('idevento'));
        
        // if($_filled && $_empty) {
        if($_filled) {
            $movimiento = $this->getModel('movimiento');
            $proceso = $this->getModel('proceso');
            $evento = $this->getModel('evento');
            
            $openEvents = $evento->count(array (
                ':idproceso' => $this->post['idproceso'],
                ':id_estado' => '1'
            ));
            
            if($openEvents > 0) {
                HTTP::JSON(Partial::createResponse(HTTP::Value(403), 'Hay un movimiento que debe cerrar primero'));
            }
            
            $this->post['idtiempo'] = 1;
            $params = Partial::prefix($this->post, ':');

            $evento->insert($params);

            if($evento->lastID() > 0) {
                //     $proceso->update($this->post['idproceso'], array (
                //         ':idestado' => '2'
                //     ));
                    
                //     $this->post['idevento'] = $evento->lastID();

                if(count($this->post['remolcadores']) > 0){
                    $idevento = $evento->lastID();
                    $remolcadores = $this->getModel('remolcando');

                    foreach ($this->post['remolcadores'] as $key => $value) {
                        $remolcadores->insert(array(
                            ':idevento' => $idevento,
                            ':idremolque' => $value
                        ));
                    }
                }

                $this->remainder($this->post['idproceso'], 'proccess');

                HTTP::JSON(Partial::createResponse(HTTP::Value(200), $this->post));
            }
        }
        
        HTTP::JSON(Partial::createResponse(HTTP::Value(400), $_filled));
    }

    function createTempTable($param, $where = 'event', $title = ''){
        if($where == 'event'){
            $where = array(':ultimo_eventoid' => $param);
        }else if($where == 'proccess'){
            $where = array(':idproceso' => $param);
        }

        $result =  Partial::arrayNames($this->getModel('list_processes')->select($where));
        $table = "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" style=\"border:2px solid #9b9b9b\">
                    <tbody>
                        <tr>
                            <td colspan=\"3\" style=\"text-align: center;\">
                                <img width=\"135\" height=\"131\" src=\"http://pilotos.soluntech.com/css/imgs/logo.png\" alt=\"header\" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan=\"3\" style=\"border-bottom:1px solid #9b9b9b; text-align: center;\">
                                <p>". $title ."</p>
                            </td>
                        </tr>
                        <tr bgcolor=\"#e7ecf1\">
                            <td colspan=\"3\" style=\"border-bottom:1px solid #9b9b9b\">
                                <span style=\"float:left\">
                                    <strong>:proceso</strong>
                                </span>
                            </td>
                        </tr>
                        <tr bgcolor=\"#e7ecf1\">
                            <td colspan=\"3\" style=\"border-bottom:1px solid #9b9b9b\">
                                <strong>Provisional:</strong> :provisional
                            </td>
                        </tr>
                        <tr>
                            <td align=\"center\" style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b\">
                                <strong>Motonave</strong>
                            </td>
                            <td align=\"center\" style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b\">
                                <strong>Agencia</strong>
                            </td>
                            <td align=\"center\" style=\"border-bottom:1px solid #9b9b9b\">
                                <strong>Maniobra</strong>
                            </td>
                        </tr>
                        <tr bgcolor=\"#e7ecf1\">
                            <td style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b;text-align:center\">
                                :motonave
                            </td>
                            <td style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b;text-align:center\">
                                :agencia
                            </td>
                            <td style=\"border-bottom:1px solid #9b9b9b;text-align:center\">
                                :maniobra
                            </td>
                        </tr>
                        <tr>
                            <td align=\"center\" style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b\">
                                <strong>Tiempo</strong>
                            </td>
                            <td align=\"center\" style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b\">
                                <strong>Fecha</strong>
                            </td>
                            <td align=\"center\" style=\"border-bottom:1px solid #9b9b9b\">
                                <strong>Hora</strong>
                            </td>
                        </tr>
                        <tr bgcolor=\"#e7ecf1\">
                            <td style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b;text-align:center\">
                                :tiempo
                            </td>
                            <td style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b;text-align:center\">
                                :fecha
                            </td>
                            <td style=\"border-bottom:1px solid #9b9b9b;text-align:center\">
                                :hora
                            </td>
                        </tr>
                        <tr>
                            <td align=\"center\" style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b\">
                                <strong>Piloto</strong>
                            </td>
                            <td align=\"center\" style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b\">
                                <strong>Terminal</strong>
                            </td>
                            <td align=\"center\" style=\"border-bottom:1px solid #9b9b9b\">
                                <strong>Muelle</strong>
                            </td>
                        </tr>
                        <tr bgcolor=\"#e7ecf1\">
                            <td style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b;text-align:center\">
                                :piloto
                            </td>
                            <td style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b;text-align:center\">
                                :terminal
                            </td>
                            <td style=\"border-bottom:1px solid #9b9b9b;text-align:center\">
                                :muelle
                            </td>
                        </tr>
                        <tr>
                            <td align=\"center\" style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b\">
                                <strong>Eslora</strong>
                            </td>
                            <td align=\"center\" style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b\">
                                <strong>Imo</strong>
                            </td>
                            <td align=\"center\" style=\"border-bottom:1px solid #9b9b9b\">
                                <strong>Manga</strong>
                            </td>
                        </tr>
                        <tr bgcolor=\"#e7ecf1\">
                            <td style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b;text-align:center\">
                                :eslora
                            </td>
                            <td style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b;text-align:center\">
                                :imo
                            </td>
                            <td style=\"border-bottom:1px solid #9b9b9b;text-align:center\">
                                :manga
                            </td>
                        </tr>
                        <tr>
                            <td align=\"center\" style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b\">
                                <strong>Trb</strong>
                            </td>
                            <td align=\"center\" style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b\">
                                <strong>Popa</strong>
                            </td>
                            <td align=\"center\" style=\"border-bottom:1px solid #9b9b9b\">
                                <strong>Proa</strong>
                            </td>
                        </tr>
                        <tr bgcolor=\"#e7ecf1\">
                            <td style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b;text-align:center\">
                                :trb
                            </td>
                            <td style=\"border-bottom:1px solid #9b9b9b;border-right:1px solid #9b9b9b;text-align:center\">
                                :popa
                            </td>
                            <td style=\"border-bottom:1px solid #9b9b9b;text-align:center\">
                                :proa
                            </td>
                        </tr>
                        <tr>
                            <td colspan=\"3\" style=\"border-bottom:1px solid #9b9b9b\">
                                <center><strong>Comentarios</strong></center>
                            </td>
                        </tr>
                        <tr bgcolor=\"#e7ecf1\">
                            <td colspan=\"3\">:observacion</td>
                        </tr>
                    </tbody>
                </table>";
        $table = Partial::fetchRows3($result, $table);
        return $table;
    }

    function remainder($param, $where = 'event', $table2 = ''){
        $table = $this->createTempTable($param, $where, 'Actual');

        $headers = "From: " . strip_tags('no-reply@example.com') . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $html = "<html>
                    <head>
                        <link href='http://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
                        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
                        <style>
                            * {
                                font-family: 'Roboto', sans-serif;
                                outline: none;
                            }
                            td {
                                width: 900px;
                            }
                        </style>
                    </head>
                    <body>". $table2 . "<div style=\"padding:15px;\"></div> " . $table ."</body>
                </html>";

        $result2 = Partial::arrayNames(
            $this->getModel('subscribed_users')->select()
        );

        $user_list = '';
        foreach ($result2 as $key => $value) {
            $user_list .= $value["correo"].", ";
        }

        if($where == 'event'){
            $where = array(':ultimo_eventoid' => $param);
        }else if($where == 'proccess'){
            $where = array(':idproceso' => $param);
        }
        $result =  Partial::arrayNames($this->getModel('list_processes')->select($where));

        $user_list = (strlen($user_list) > 0) ? substr($user_list, 0, -2) : $user_list;
        mail($user_list, 'Proceso '.$result[0]['idproceso'].' - Pilotos', $html, $headers);

        $this->push($result[0]['idproceso'], '');
    }
    
    function add_move () {
        $_filled = Partial::_filled($this->post, array ('idevento', 'idtiempo', 'fecha', 'hora'));
        
        if($_filled) {
            $movimiento = $this->getModel('movimiento');
            
            if($this->post['idtiempo'] == 1) {
                $aBordo = $movimiento->count(array (
                    ':idevento' => $this->post['idevento'],
                    ':idtiempo' => '1'
                ));

                if($aBordo > 0) {
                    HTTP::JSON(Partial::createResponse(HTTP::Value(403), 'Tiempo piloto a bordo ya existe'));
                }
            }
            
            if($this->post['idtiempo'] == 2) {
                $desembarco = $movimiento->count(array (
                    ':idevento' => $this->post['idevento'],
                    ':idtiempo' => '2'
                ));

                if($desembarco > 0) {
                    HTTP::JSON(Partial::createResponse(HTTP::Value(403), 'Tiempo desembarco piloto ya existe'));
                }
            }
            
            $params = Partial::prefix($this->post, ':');
            
            $movimiento->insert($params);

            $this->remainder($this->post['idevento'], 'event');
            
            HTTP::JSON(200);
        }
        
        HTTP::JSON(400);
    }

    function listas () {
        $params = array ();
        
        $params['piloto'] = Partial::arrayNames(
            $this->getModel('piloto')->select()
        );
        
        $params['tiempo'] = Partial::arrayNames(
            $this->getModel('tiempo')->select()
        );
        
        $params['estado_'] = Partial::arrayNames(
            $this->getModel('estado_')->select()
        );
        
        $params['terminal'] = Partial::arrayNames(
            $this->getModel('terminal')->select()
        );

        $params['remolque'] = Partial::arrayNames(
            $this->getModel('remolque')->select()
        );
        
        HTTP::JSON(Partial::createResponse(HTTP::Value(200), $params));
    }
    
    function duplicar () {
        if (Partial::_filled($this->get, array ('idevento'))) {
            $movimiento = $this->getModel('movimiento');
            
            $result = $movimiento->select(array(
                ':idevento' => $this->get['idevento']
            ), ' ORDER BY idmovimiento DESC LIMIT 1');
            
            if(count($result) > 0) {
                $params = Partial::arrayNames($result, array ('idmovimiento', 'idtiempo'));
                $movimiento->insert(Partial::prefix($params[0], ':'));
                
                HTTP::JSON(200);
            }
            
            HTTP::JSON(403);
        }
        
        HTTP::JSON(400);
    }

    function remove () {
        $_filled = Partial::_filled($this->get, array('idmovimiento'));
        $evento = Partial::arrayNames($this->getModel('movimientos_procesos')->select(array(
            ':idmovimiento' => $this->get['idmovimiento']
        )));

        // $table2 = '';
        $table2 = $this->createTempTable($evento[0]['ultimo_eventoid'], 'event', 'Previo al Cambio');
        
        if($_filled) {
            $this->getModel('movimiento')->delete($this->get['idmovimiento']);
            $this->remainder($evento[0]['ultimo_eventoid'], 'event', $table2);
            HTTP::JSON(200);
        }
        
        HTTP::JSON(400);
    }

    function removeEvent () {
        $_filled = Partial::_filled($this->get, array('idevento'));
        if($_filled) {
            $this->getModel('evento')->delete($this->get['idevento']);
            $this->remainder($this->get['idevento'], 'event');
            HTTP::JSON(200);
        }
        HTTP::JSON(400);
    }
    
    function finalize () {
        $_get = Partial::_filled($this->get, array ('idevento'));
        
        if($_get) {
            $movimiento = $this->getModel('movimiento');
            
            $aBordo = $movimiento->count(array (
                ':idevento' => $this->get['idevento'],
                ':idtiempo' => '1'
            ));
            
            $desembarco = $movimiento->count(array (
                ':idevento' => $this->get['idevento'],
                ':idtiempo' => '2'
            ));
            
            if($aBordo > 0 && $desembarco > 0) {
                $this->getModel('evento')->update($this->get['idevento'], array (
                    ':id_estado' => '2'
                ));

                $this->remainder($this->get['idevento'], 'event');

                HTTP::JSON(200);
            }
            
            HTTP::JSON(Partial::createResponse(HTTP::Value(403), 'No se ha encontrado el tiempo de desembarco piloto o piloto a bordo'));
        }
        
        HTTP::JSON(400);
    }

    function update() {
        $_filled = Partial::_filled($this->put, array('idevento', 'muelle', 'provisional'));

        if($_filled) {
            $params = Partial::prefix($this->put, ':');
            $evento = $this->getModel('evento');
            $evento->update($this->put['idevento'], $params);
            if(!isset($this->put['idpiloto2'])){
                $evento->updateAtNull($this->put['idevento'], array(
                    ':idpiloto2' => 'null'
                ));
            }

            $this->remainder($this->put['idevento'], 'event');

            HTTP::JSON(Partial::createResponse(HTTP::Value(200), ''));
        }

        HTTP::JSON(400);
    }

    function push($idproceso, $option){
        $result =  Partial::arrayNames($this->getModel('lista_proceso')->select(array(
            ':idproceso' => $idproceso
        )));

        // echo count($result);
        if(count($result) == 1){
            // echo '--->push2';
            $result2 = Partial::arrayNames(
                $this->getModel('subscribed_users')->select()
            );

            $result = $result[0];
            $result['eventos'] = (integer) $result['eventos'];

            if($option == "observacion"){
                $finalize = " tiene una observacion";
            }else if($result['estado'] == "Creado" && $result['eventos'] == 0){
                $finalize = " ha sido creado";
            }else if($result['estado'] == "Creado" && $result['eventos'] > 0){
                $finalize = " ha sido actualizado";
            }else if($result['estado'] == "En Proceso"){
                $finalize = " ha sido en proceso";
            }else if($result['estado'] == "Finalizado"){
                $finalize = " ha sido finalizado";
            }

            $messagge = "El proceso " . $result['nombre'] . $finalize;
            foreach ($result2 as $key => $value) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/x-www-form-urlencoded;charset=utf-8"
                ));
                 
                // definimos la URL a la que hacemos la petición
                curl_setopt($ch, CURLOPT_URL,"http://pilotosbarranquilla.com/notifi3r/push/send");
                // definimos el número de campos o parámetros que enviamos mediante POST
                curl_setopt($ch, CURLOPT_POST, 1);
                // definimos cada uno de los parámetros
                $params = "user_id={$value["idusuario"]}&message={$messagge}";
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                 
                // recibimos la respuesta y la guardamos en una variable
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $remote_server_output = curl_exec ($ch);

                // cerramos la sesión cURL
                // var_dump($remote_server_output);
                curl_close ($ch);
            }
        }else{
            // HTTP::JSON(500);
            echo $idproceso;
        }
    }
}

