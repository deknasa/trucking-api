<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailReminderOli extends Mailable
{
    use Queueable, SerializesModels;
    protected $data;
    protected $tipe;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, string $tipe )
    {
        $this->data = $data;
        $this->tipe = $tipe;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->tipe == 'mesin') {
            return $this->view('mails.reminder.oli')->with('data', $this->data)->subject(json_decode($this->data)[0]->judul);
        }else if ($this->tipe == 'saringanhawa') {
            return $this->view('mails.reminder.saringanhawa')->with('data', $this->data)->subject(json_decode($this->data)[0]->judul);
        }else if ($this->tipe == 'perseneling') {
            return $this->view('mails.reminder.perseneling')->with('data', $this->data)->subject(json_decode($this->data)[0]->judul);
        }else if ($this->tipe == 'oligardan') {
            return $this->view('mails.reminder.oligardan')->with('data', $this->data)->subject(json_decode($this->data)[0]->judul);
        }else if ($this->tipe == 'ServiceRutin') {
            return $this->view('mails.reminder.ServiceRutin')->with('data', $this->data)->subject(json_decode($this->data)[0]->judul);
        }
        
    }
}
