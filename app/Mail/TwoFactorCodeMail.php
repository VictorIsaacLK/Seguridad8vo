<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;

    /**
     * Crear una nueva instancia de mensaje.
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Definir el asunto del correo.
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Código de Verificación - Inicio de Sesión',
        );
    }

    /**
     * Definir el contenido del mensaje.
     */
    public function content()
    {
        return new Content(
            view: 'emails.two_factor_code',
            with: [
                'code' => $this->code
            ],
        );
    }

    /**
     * No se adjuntan archivos en este correo.
     */
    public function attachments()
    {
        return [];
    }
}
