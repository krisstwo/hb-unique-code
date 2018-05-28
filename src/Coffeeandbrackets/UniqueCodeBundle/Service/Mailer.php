<?php

namespace Coffeeandbrackets\UniqueCodeBundle\Service;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class Mailer {

    private $mailer;
    private $templating;
    private $template;
    private $subject;
    private $from;
    private $fromName;
    private $to;
    private $bcc;
    private $params = array();
    private $body;

    public function __construct( $mailer, EngineInterface $templating ) {

        $this->mailer = $mailer;
        $this->templating = $templating;

    }

    /**
     * @param array $config
     * send mail
     * @param null $contentType
     */
    public function sendMessage( array $config, $contentType = null ) {

        $this->explodeConfigAndSet( $config );
        if(!empty($config['template']))
            $this->generateBodyFromTemplate();

        $message = \Swift_Message::newInstance()
            ->setSubject( $this->subject )
            ->setFrom( $this->from, $this->fromName )
            ->setTo( $this->to )
            ->setBcc( $this->bcc )
            ->setBody( $this->body , $contentType );

        $this->mailer->send( $message );

    }

    /**
     * @param array $config
     */
    public function explodeConfigAndSet( array $config ) {

        foreach ( $config as $k => $v ) {

            switch( $k ) {

                case 'from':
                    $this->setFrom( $v );
                    break;

                case 'fromName':
                    $this->setFromName( $v );
                    break;

                case 'to':
                    $this->setTo( $v );
                    break;

                case 'bcc':
                    $this->setBcc( $v );
                    break;

                case 'subject':
                    $this->setSubject( $v );
                    break;

                case 'template':
                    $this->setTemplate( $v );
                    break;

                case 'body':
                    $this->setBody( $v );
                    break;

                case 'params':
                    $this->setParams( $v );
                    break;

                default:
                    break;
            }

        }

    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom( $from ) {
        $this->from = $from;
    }

    /**
     * @return mixed
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @param string $fromName
     */
    public function setFromName( $fromName ) {
        $this->fromName = $fromName;
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     */
    public function setTo( $to ) {
        $this->to = $to;
    }

    /**
     * @return mixed
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * @param string $bcc
     */
    public function setBcc( $bcc ) {
        $this->bcc = $bcc;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject( $subject ) {
        $this->subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param $template
     */
    public function setTemplate( $template ) {
        $this->template = $template;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param null $params
     */
    public function setParams( $params = null ) {
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * parameter template
     */
    private function generateBodyFromTemplate() {

        $this->body = $this->templating->render( $this->template, $this->params );

    }
}