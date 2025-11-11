<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use App\Models\SmtpSettings;
use App\Models\Company;
use App\Models\AppSettings;
use App\Enums\App;
use App\Services\ImageService;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $appSettingsRecordId;
    protected $companyId;
    protected $emailAddresses;
    public $subject;
    protected $content;
    protected $fromEmail;
    protected $fromName;
    protected $companyLogo;
    protected $attachmentPath;

    /**
     * Create a new content instance.
     */
    public function __construct($data)
    {
        $this->appSettingsRecordId = App::APP_SETTINGS_RECORD_ID->value;
        $this->companyId = App::APP_SETTINGS_RECORD_ID->value;
        $this->emailAddresses = $data['email'];
        $this->subject = $data['subject'];
        $this->content = $data['content'];
        $this->attachmentPath = $data['attachment_path'];

        //Get Dynamic smtp setup
        $this->setupEmailConfig();

        
    }

    public function setupCompanyLogo()
    {
         $imageService = new ImageService();
         $company = AppSettings::findOrFail($this->appSettingsRecordId)->first();
         if($company){
            $this->companyLogo = $imageService->resizeImage(
                'images/app-logo/'.$company->colored_logo,
                300,
                200
            );
         }else{
            $this->companyLogo = null;
         }
         
    }

    public function setupFromEmailConfig(){
        //Check Email API
        $this->fromEmail = app('company')['email'];
        $this->fromName = app('company')['name'];

        if(!$this->fromEmail){
            return response()->json([
                'status' => false,
                'message' => __('message.company_email_address_is_empty'),
            ], 409);
        }
    }

    /**
     * Get SMTP Configuration settings from database
     * and setup it in email configutation settings
     * */
    public function setupEmailConfig(){

        //$this->setupCompanyLogo();

        //Configure from emaill setup
        $this->setupFromEmailConfig();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            //from: new Address($this->fromEmail, $this->fromName),
            to: $this->emailAddresses,
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mails.send_email',
            with: [
                        'content' => $this->content,
                        //'companyLogo' => $this->companyLogo,
                    ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        
        if(!empty($this->attachmentPath)){
            $attachments = [
                Attachment::fromPath($this->attachmentPath),
            ];
        }
        return $attachments;
    }
}