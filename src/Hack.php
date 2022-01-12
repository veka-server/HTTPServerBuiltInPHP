<?php

namespace VekaServer\HTTPServerBuiltInPHP;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Hack implements MiddlewareInterface
{

    private $asset_directory ;

    public function __construct(String $asset_directory) {
        $this->asset_directory = $asset_directory ;
    }

    /**
     * si nous utilisons le server http de PHP
     * ont utilise le hack pour les documents static
     * pour les autres server http il faut configurer le vhost correctement
     * NE PAS UTILISER EN PROD !!!!
     */
    public function phpBuilInServerHttp($uri){

        /** seulement pour le serveur http de PHP */
        if (php_sapi_name() != 'cli-server') {
            return;
        }

        $url = strtok($uri,'?');
        $public_directory = realpath($this->asset_directory);
        $file_path = $public_directory.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, explode('/', $url));
        $file_path = realpath($file_path);

        /** verifie que le fichier est toujours dans le dossier public */
        if(strpos( $file_path , $public_directory ) !== 0){
            return ;
        }

        if(!is_file($file_path))
            return ;
        $path_parts = pathinfo($file_path);

        switch($path_parts["extension"]){

            /** exclure les fichiers php */
            case 'php':
            case 'php1':
            case 'php2':
            case 'php3':
            case 'php4':
            case 'php5':
            case 'php6':
            case 'php7':
            case 'php8':
            case 'php9':
            case 'php10':
                return ;

            case 'css':
                $ct = 'text/css';
                break;

            default:
                $ct = 'text/plain';
                break;

        }

        header('Content-type: '.$ct);
        readfile($file_path);
        die();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $this->phpBuilInServerHttp($request->getUri()->getPath());

        $response = $handler->handle($request);

        return $response;
    }
}