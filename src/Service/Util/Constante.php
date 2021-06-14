<?php

namespace App\Service\Util;

class Constante
{
    const COD_CONFIG = 'SYSTEM'; //Código de configuración

    const PHONE_LENGTH = 9; //Logintud de un telefonico
    const USER_CODE_TIME = 60*15; //Duracion de validez del codigo de usuario
    const ALERT_CONTACT_MAX = 4; //Maxima cantidad de contactos
    const MAX_FILE_SIZE = 5*1024**2; //Tamaño máximo de archivo permitido
    const IMAGE_EXTENSIONS = ['png', 'jpg']; // Extensiones permitidas para las imagenes
    const ALERT_RADIO = 2000; // Radio de la Alerta en metros

    const HTTP_CREATED = 201; //Se ha creado un nuevo recurso como resultado
    const HTTP_ACCEPTED = 202; //Aceptado, proceso encolado sin respuesta
    const HTTP_BAD_REQUEST = 400; //Petición Incorrecta
    const HTTP_UNAUTHORIZED = 401; //Se necesita autenticarse
    const HTTP_FORBIDDEN = 403; //Permiso insifuciente
    const HTTP_NOT_FOUND = 404; //Recurso no encontrado
    const HTTP_CONFLICT = 409; //Conflicto con la peticion del cliente
    const HTTP_SERVER_ERROR = 500; //Error interno en el servidor

    const TYPE_EMEGENCY = "emergency"; // Tipo emergencia 

    const EVENT_EMEGENCY_INIT = "EMERGENCY_INIT"; //Evento que indica el inicio de la emergencia
    const EVENT_EMEGENCY_UPDATE = "EMERGENCY_UPDATE"; //Evento que indica una modificacion
    const EVENT_EMEGENCY_END = "EMERGENCY_END"; //Evento que indica el fin de la emergencia
    
    const PATH_TMP = '../storage/tmp';
    const PATH_OPERACION = '../storage/operacion';
}