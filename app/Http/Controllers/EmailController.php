<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Http\Requests\EmailRequest;
use App\Enums\App;
use App\Services\EmailService;
use Illuminate\Support\Facades\Storage;

class EmailController extends Controller
{
    protected $appSettingsRecordId;
    protected $companyId;
    private $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->appSettingsRecordId = App::APP_SETTINGS_RECORD_ID->value;
        $this->companyId = App::APP_SETTINGS_RECORD_ID->value;
        $this->emailService = $emailService;
    }

    public function create(): View
    {
        return view('email.create');
    }
    
    /**
     * Send Email 
     * 
     * return @return \Illuminate\Http\JsonResponse
     * 
     * */
    public function send(EmailRequest $request) : JsonResponse  {
        $validatedData = $request->validated();

        $filename = null;

        $autoAttachFileName = null;

        /**
         * attachment Upload
         * */
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $filename = $this->uploadAttachment($request->file('attachment'));
        }

        /**
         * Based on model get the invoice attached
         * */
        $emailData = [
            'email'     => $validatedData['email'],
            'subject'   => $validatedData['subject'],
            'content'   => $validatedData['content'],
            'attachment_path' => $filename,
        ];

        /**
         * @return Json Response
         * */
        return $this->emailService->send($emailData);
    }

    private function uploadAttachment($attachment): string
    {
        // Generate a unique filename for the attachment
        $random = uniqid();
        $filename = $random . '.' . $attachment->getClientOriginalExtension();
        $directory = 'attachments';

        // Create the directory if it doesn't exist
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // Store the file in the 'attachments' directory with the specified filename
        Storage::disk('public')->put($directory . '/' . $filename, file_get_contents($attachment));

        // Get the full path to the stored file
        $attachmentPath = storage_path('app/public/' . $directory . '/' . $filename);
  
        // Return the attachment path
        return $attachmentPath;
    }
}