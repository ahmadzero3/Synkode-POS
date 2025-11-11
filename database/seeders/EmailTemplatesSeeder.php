<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        /**
         * 
         * ORDER CREATED
         * */
        /*EmailTemplate::create([
            'name'                  => 'ORDER CREATED',
            'subject'               => 'Congratulations!! your order is created',
            'content'               => 'Dear :customer_first_name :customer_last_name , service order is created. Your order id is :order_id. Thank you',
            'keys'                  => ':customer_first_name
:customer_last_name
:order_id
:company_name
:order_date',
            'delete_flag'           => 1,
        ]);*/


        /**
         * 
         * SALE INVOICE
         * */
        $content = <<<EOT
Dear [Customer Name],

Please find attached the invoice for your recent sale.

Invoice Details:

   Invoice Number: [Invoice Number]
   Sale Date: [Sale Date]
   Due Date: [Due Date]
   Total: [Total Amount]
   Paid Amount: [Paid Amount]
   Invoice Balance: [Balance Amount]

If you have any questions or require further assistance, please don't hesitate to contact us at [Your Email Address] or [Your Mobile Number].

Thank you for your business.

Sincerely,
[Your Company Name]
[Your Mobile Number]
EOT;
        $keys = <<<EOT
[Invoice Number]

[Customer Name]

[Sale Date]

[Due Date]

[Total Amount]

[Paid Amount]

[Balance Amount]

[Your Email Address]

[Your Mobile Number]

[Your Company Name]
EOT;

        EmailTemplate::create([
            'name'                  => 'SALE INVOICE',
            'subject'               => 'Invoice #[Invoice Number] - [Customer Name]',
            'content'               => $content,
            'keys'                  => $keys,
            'delete_flag'           => 1,
        ]);

        /**
         * SALE ORDER
         * 
         * */
        $content = <<<EOT
Dear [Customer Name],

Please find attached the details of sale order.

Order Details:

    Order Number: [Order Number]

    Order Date: [Order Date]

    Due Date: [Due Date]

    Total: [Total Amount]

    Paid Amount: [Paid Amount]

    Invoice Balance: [Balance Amount]

If you have any questions or require further assistance, please don't hesitate to contact us at [Your Email Address] or [Your Mobile Number].

Thank you for your business.

Sincerely,

[Your Company Name]

[Your Mobile Number]
EOT;

        $keys = <<<EOT
[Order Number]

[Customer Name]

[Order Date]

[Due Date]

[Total Amount]

[Paid Amount]

[Balance Amount]

[Your Email Address]

[Your Mobile Number]

[Your Company Name]
EOT;

        EmailTemplate::create([
            'name'                  => 'SALE ORDER',
            'subject'               => 'Sale Order #[Order Number] - [Customer Name]',
            'content'               => $content,
            'keys'                  => $keys,
            'delete_flag'           => 1,
        ]);

        /**
         * SALE RETURN
         * 
         * */
        $content = <<<EOT
Dear [Customer Name],

Please find attached the details of sale return/credit note.

Sale Return Details:

    Return Number: [Return Number]

    Return Date: [Return Date]

    Total: [Total Amount]

    Return Amount: [Return Amount]

    Balance: [Balance Amount]

If you have any questions or require further assistance, please don't hesitate to contact us at [Your Email Address] or [Your Mobile Number].

Thank you for your business.

Sincerely,

[Your Company Name]

[Your Mobile Number]
EOT;

        $keys = <<<EOT
[Return Number]

[Customer Name]

[Return Date]

[Total Amount]

[Return Amount]

[Balance Amount]

[Your Email Address]

[Your Mobile Number]

[Your Company Name]
EOT;

        EmailTemplate::create([
            'name'                  => 'SALE RETURN',
            'subject'               => 'Sale Return/Credit Note #[Return Number] - [Customer Name]',
            'content'               => $content,
            'keys'                  => $keys,
            'delete_flag'           => 1,
        ]);


        /**
         * 
         * PURCHASE BILL
         * */
        $content = <<<EOT
Dear [Supplier Name],

Please find attached the bill for your recent purchase.

Bill Details:

   Bill Number: [Bill Number]
   Purchase Date: [Purchase Date]
   Total: [Total Amount]
   Paid Amount: [Paid Amount]
   Bill Balance: [Balance Amount]

If you have any questions or require further assistance, please don't hesitate to contact us at [Your Email Address] or [Your Mobile Number].

Thank you for your business.

Sincerely,
[Your Company Name]
[Your Mobile Number]
EOT;
        $keys = <<<EOT
[Bill Number]

[Supplier Name]

[Purchase Date]

[Total Amount]

[Paid Amount]

[Balance Amount]

[Your Email Address]

[Your Mobile Number]

[Your Company Name]
EOT;

        EmailTemplate::create([
            'name'                  => 'PURCHASE BILL',
            'subject'               => 'Purchase Bill #[Bill Number] - [Supplier Name]',
            'content'               => $content,
            'keys'                  => $keys,
            'delete_flag'           => 1,
        ]);


        /**
         * PURCHASE ORDER
         * 
         * */
        $content = <<<EOT
Dear [Supplier Name],

Please find attached the details of purchase order.

Purchase Order Details:

    Order Number: [Order Number]

    Order Date: [Order Date]

    Due Date: [Due Date]

    Total: [Total Amount]

    Paid Amount: [Paid Amount]

    Bill Balance: [Balance Amount]

If you have any questions or require further assistance, please don't hesitate to contact us at [Your Email Address] or [Your Mobile Number].

Thank you for your business.

Sincerely,

[Your Company Name]

[Your Mobile Number]
EOT;

        $keys = <<<EOT
[Order Number]

[Customer Name]

[Order Date]

[Due Date]

[Total Amount]

[Paid Amount]

[Balance Amount]

[Your Email Address]

[Your Mobile Number]

[Your Company Name]
EOT;

        EmailTemplate::create([
            'name'                  => 'PURCHASE ORDER',
            'subject'               => 'Purchase Order #[Order Number] - [Supplier Name]',
            'content'               => $content,
            'keys'                  => $keys,
            'delete_flag'           => 1,
        ]);

        /**
         * SALE ORDER
         * 
         * */
        $content = <<<EOT
Dear [Supplier Name],

Please find attached the details of purchase return/credit note.

Purchase Return Details:

    Return Number: [Return Number]

    Return Date: [Return Date]

    Total: [Total Amount]

    Return Amount: [Return Amount]

    Balance: [Balance Amount]

If you have any questions or require further assistance, please don't hesitate to contact us at [Your Email Address] or [Your Mobile Number].

Thank you for your business.

Sincerely,

[Your Company Name]

[Your Mobile Number]
EOT;

        $keys = <<<EOT
[Return Number]

[Supplier Name]

[Return Date]

[Total Amount]

[Return Amount]

[Balance Amount]

[Your Email Address]

[Your Mobile Number]

[Your Company Name]
EOT;

        EmailTemplate::create([
            'name'                  => 'PURCHASE RETURN',
            'subject'               => 'Purchase Return/Debit Note #[Return Number] - [Supplier Name]',
            'content'               => $content,
            'keys'                  => $keys,
            'delete_flag'           => 1,
        ]);
    }
}
