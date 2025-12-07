<?php
namespace Views\vistas;

abstract class VistaApi{

    // C�digo de error
    public $estado;

    public abstract function imprimir($cuerpo);
}