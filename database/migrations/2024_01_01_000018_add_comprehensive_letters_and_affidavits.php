<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Category;
use App\Models\Template;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $lettersCat = Category::firstOrCreate(
            ['slug' => 'letters'],
            ['name' => 'Letters', 'description' => 'Official letters and correspondence', 'icon' => '✉️', 'is_active' => true, 'sort_order' => 1]
        );
        $affidavitsCat = Category::firstOrCreate(
            ['slug' => 'affidavits'],
            ['name' => 'Affidavits', 'description' => 'Sworn legal statements and affidavits', 'icon' => '⚖️', 'is_active' => true, 'sort_order' => 2]
        );

        $templates = [
            // =========================================================================
            // LETTERS
            // =========================================================================
            [
                'category_id' => $lettersCat->id,
                'name'        => 'Resignation Letter',
                'slug'        => 'resignation-letter',
                'description' => 'A formal letter of resignation from employment with notice period and handover commitments.',
                'price'       => 50.00,
                'sort_order'  => 6,
                'is_active'   => true,
                'schema'      => [
                    ['name' => 'employee_name',    'label' => 'Your Full Name',             'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Kelvin Kipchumba'],
                    ['name' => 'employee_address', 'label' => 'Your Residential / P.O. Box', 'type' => 'text',   'required' => true,  'placeholder' => 'P.O. Box 450, Nairobi'],
                    ['name' => 'date',             'label' => 'Date of Notice',             'type' => 'date',     'required' => true],
                    ['name' => 'supervisor_title', 'label' => 'Supervisor Title / Recipient', 'type' => 'text',  'required' => true,  'placeholder' => 'e.g. The Human Resource Director'],
                    ['name' => 'company_name',     'label' => 'Company Name',               'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Equity Bank Kenya'],
                    ['name' => 'position',         'label' => 'Your Current Job Title',     'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Customer Relationship Officer'],
                    ['name' => 'last_working_day', 'label' => 'Effective Last Working Day', 'type' => 'date',     'required' => true],
                    ['name' => 'reason',           'label' => 'Reason for Resignation (Optional)', 'type' => 'textarea', 'required' => false, 'placeholder' => 'e.g. to pursue further studies / a new career opportunity'],
                ],
                'definition'  => [
                    'title' => 'Resignation Letter',
                    'sections' => [
                        [
                            'type'    => 'header',
                            'content' => "{{employee_name}}\n{{employee_address}}\nDate: {{date}}",
                        ],
                        [
                            'type'    => 'recipient',
                            'content' => "{{supervisor_title}}\n{{company_name}}",
                        ],
                        [
                            'type'    => 'subject',
                            'content' => "RE: NOTICE OF RESIGNATION FROM POSITION OF {{position}}",
                        ],
                        [
                            'type'    => 'salutation',
                            'content' => "Dear Sir/Madam,",
                        ],
                        [
                            'type'    => 'body',
                            'content' => "Please accept this letter as formal notification that I am resigning from my position as {{position}} at {{company_name}}. My last working day with the organization will be {{last_working_day}}.\n\n{{reason}}\n\nI want to express my sincere appreciation for the opportunities and professional mentorship I have experienced during my tenure with {{company_name}}. I am grateful for the collaboration of my colleagues and management.\n\nDuring my remaining notice period, I will complete all outstanding assignments and facilitate a seamless transition and handover of my responsibilities.",
                        ],
                        [
                            'type'    => 'closing',
                            'content' => "Yours sincerely,\n\n\n_________________________________\n{{employee_name}}\n{{position}}",
                        ],
                    ],
                ],
            ],

            [
                'category_id' => $lettersCat->id,
                'name'        => 'Formal Complaint Letter',
                'slug'        => 'complaint-letter',
                'description' => 'Official complaint to a company, service provider, landlord, or government department.',
                'price'       => 50.00,
                'sort_order'  => 7,
                'is_active'   => true,
                'schema'      => [
                    ['name' => 'complainant_name',    'label' => 'Your Full Name',             'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Faith Wambui'],
                    ['name' => 'complainant_contact', 'label' => 'Your Contact Info (Phone/Address)', 'type' => 'text', 'required' => true, 'placeholder' => 'P.O. Box 789, Nairobi | 07XXXXXXXX'],
                    ['name' => 'date',                'label' => 'Date',                       'type' => 'date',     'required' => true],
                    ['name' => 'recipient_title',     'label' => 'Recipient / Department',     'type' => 'text',     'required' => true,  'placeholder' => 'e.g. The Customer Experience Manager'],
                    ['name' => 'organisation',        'label' => 'Company / Institution Name', 'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Safaricom PLC / Nairobi Water Co.'],
                    ['name' => 'complaint_subject',   'label' => 'Complaint Subject',          'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Unresolved Billing Overcharge / Failure of Water Supply'],
                    ['name' => 'incident_details',    'label' => 'Incident Details & Facts',   'type' => 'textarea', 'required' => true,  'placeholder' => 'Describe dates, transaction reference, what happened, and previous attempts to resolve.'],
                    ['name' => 'demanded_action',     'label' => 'Action Demanded / Resolution', 'type' => 'textarea', 'required' => true, 'placeholder' => 'e.g. Full refund of KSh 15,000 within 7 working days, restoration of services, and formal apology.'],
                ],
                'definition'  => [
                    'title' => 'Formal Complaint Letter',
                    'sections' => [
                        [
                            'type'    => 'header',
                            'content' => "{{complainant_name}}\n{{complainant_contact}}\nDate: {{date}}",
                        ],
                        [
                            'type'    => 'recipient',
                            'content' => "{{recipient_title}}\n{{organisation}}",
                        ],
                        [
                            'type'    => 'subject',
                            'content' => "RE: FORMAL COMPLAINT REGARDING {{complaint_subject}}",
                        ],
                        [
                            'type'    => 'salutation',
                            'content' => "Dear Sir/Madam,",
                        ],
                        [
                            'type'    => 'body',
                            'content' => "I am writing to formally raise a serious grievance concerning {{complaint_subject}} involving your organization.\n\n{{incident_details}}\n\nThis situation has caused significant inconvenience and dissatisfaction. In order to amicably resolve this matter, I request that the following action be taken without delay:\n\n{{demanded_action}}\n\nKindly acknowledge receipt of this letter within five (5) business days and communicate your resolution plan.",
                        ],
                        [
                            'type'    => 'closing',
                            'content' => "Yours faithfully,\n\n\n_________________________________\n{{complainant_name}}",
                        ],
                    ],
                ],
            ],

            [
                'category_id' => $lettersCat->id,
                'name'        => 'Leave Application Letter',
                'slug'        => 'leave-application',
                'description' => 'Request for annual leave, compassionate leave, sick leave, or paternity/maternity leave.',
                'price'       => 50.00,
                'sort_order'  => 8,
                'is_active'   => true,
                'schema'      => [
                    ['name' => 'employee_name',    'label' => 'Your Full Name',             'type' => 'text',   'required' => true,  'placeholder' => 'e.g. Peter Otieno Odhiambo'],
                    ['name' => 'employee_number',  'label' => 'Employee / Staff Number',    'type' => 'text',   'required' => false, 'placeholder' => 'e.g. EMP-2041'],
                    ['name' => 'position',         'label' => 'Job Title & Department',     'type' => 'text',   'required' => true,  'placeholder' => 'e.g. IT Support Specialist, ICT Dept'],
                    ['name' => 'date',             'label' => 'Application Date',           'type' => 'date',   'required' => true],
                    ['name' => 'supervisor_name',  'label' => 'Supervisor Name / Manager',  'type' => 'text',   'required' => true,  'placeholder' => 'e.g. Mrs. Mary Wangari'],
                    ['name' => 'leave_type',       'label' => 'Type of Leave',              'type' => 'text',   'required' => true,  'placeholder' => 'e.g. Annual Leave, Paternity Leave, Sick Leave'],
                    ['name' => 'start_date',       'label' => 'Leave Start Date',           'type' => 'date',   'required' => true],
                    ['name' => 'end_date',         'label' => 'Leave End Date',             'type' => 'date',   'required' => true],
                    ['name' => 'handover_person',  'label' => 'Colleague Handled To',       'type' => 'text',   'required' => false, 'placeholder' => 'e.g. Jane Kimani'],
                ],
                'definition'  => [
                    'title' => 'Leave Application Letter',
                    'sections' => [
                        [
                            'type'    => 'header',
                            'content' => "{{employee_name}}\nStaff ID: {{employee_number}}\n{{position}}\nDate: {{date}}",
                        ],
                        [
                            'type'    => 'recipient',
                            'content' => "To: {{supervisor_name}}\nDepartment Head / HR Manager",
                        ],
                        [
                            'type'    => 'subject',
                            'content' => "RE: APPLICATION FOR {{leave_type}} (FROM {{start_date}} TO {{end_date}})",
                        ],
                        [
                            'type'    => 'salutation',
                            'content' => "Dear {{supervisor_name}},",
                        ],
                        [
                            'type'    => 'body',
                            'content' => "I am writing to formally request approval for {{leave_type}} starting on {{start_date}} and concluding on {{end_date}}. I will resume duty on the next regular working day.\n\nPrior to my departure, I will ensure that all urgent tasks are up to date. I have briefed {{handover_person}} to oversee critical client queries during my absence.\n\nI will remain reachable on mobile for urgent consultations if necessary. Thank you for considering my request.",
                        ],
                        [
                            'type'    => 'closing',
                            'content' => "Yours sincerely,\n\n\n_________________________________\n{{employee_name}}",
                        ],
                    ],
                ],
            ],

            [
                'category_id' => $lettersCat->id,
                'name'        => 'Formal Demand Letter (Debt / Notice)',
                'slug'        => 'demand-letter',
                'description' => 'Notice demanding immediate settlement of debt or fulfillment of contract before legal action.',
                'price'       => 50.00,
                'sort_order'  => 9,
                'is_active'   => true,
                'schema'      => [
                    ['name' => 'creditor_name',    'label' => 'Creditor / Your Full Name',   'type' => 'text',     'required' => true,  'placeholder' => 'e.g. David Mutua'],
                    ['name' => 'creditor_address', 'label' => 'Your Physical / P.O. Box',   'type' => 'text',     'required' => true,  'placeholder' => 'P.O. Box 1020, Nairobi'],
                    ['name' => 'date',             'label' => 'Date',                       'type' => 'date',     'required' => true],
                    ['name' => 'debtor_name',      'label' => 'Debtor / Defaulter Name',     'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Jackson Muriuki'],
                    ['name' => 'debtor_address',   'label' => 'Debtor Address',             'type' => 'text',     'required' => true,  'placeholder' => 'P.O. Box 334, Nakuru'],
                    ['name' => 'amount_owed',      'label' => 'Amount Owed (KSh)',          'type' => 'text',     'required' => true,  'placeholder' => 'e.g. KSh 85,000'],
                    ['name' => 'debt_origin',      'label' => 'Transaction / Agreement Basis', 'type' => 'textarea', 'required' => true, 'placeholder' => 'e.g. Supply of construction timber under Invoice #102 dated 14th June 2026.'],
                    ['name' => 'deadline_days',    'label' => 'Notice Deadline (e.g. 7 or 14 days)', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. 7 (seven) days'],
                ],
                'definition'  => [
                    'title' => 'Formal Demand Letter',
                    'sections' => [
                        [
                            'type'    => 'header',
                            'content' => "FROM:\n{{creditor_name}}\n{{creditor_address}}\nDate: {{date}}",
                        ],
                        [
                            'type'    => 'recipient',
                            'content' => "TO:\n{{debtor_name}}\n{{debtor_address}}",
                        ],
                        [
                            'type'    => 'subject',
                            'content' => "RE: DEMAND FOR IMMEDIATE PAYMENT OF OUTSTANDING DEBT ({{amount_owed}})",
                        ],
                        [
                            'type'    => 'salutation',
                            'content' => "Dear {{debtor_name}},",
                        ],
                        [
                            'type'    => 'body',
                            'content' => "TAKE NOTICE that you are indebted to me in the principal sum of {{amount_owed}} arising from:\n\n{{debt_origin}}\n\nDespite repeated verbal and written reminders, you have failed, neglected, and/or refused to settle this liquidated amount.\n\nDEMAND IS HEREBY MADE for the full settlement of {{amount_owed}} within {{deadline_days}} from the date hereof.\n\nTAKE FURTHER NOTICE that in the event of default, I shall proceed to initiate legal debt recovery proceedings against you in a court of competent jurisdiction without further reference to you, wherein you will be held liable for the principal claim together with court costs, statutory interest, and advocate fees.",
                        ],
                        [
                            'type'    => 'closing',
                            'content' => "Yours faithfully,\n\n\n_________________________________\n{{creditor_name}}",
                        ],
                    ],
                ],
            ],

            [
                'category_id' => $lettersCat->id,
                'name'        => 'Sponsorship Request Letter',
                'slug'        => 'sponsorship-request',
                'description' => 'Formal proposal requesting financial or material sponsorship for education, sport, or community projects.',
                'price'       => 50.00,
                'sort_order'  => 10,
                'is_active'   => true,
                'schema'      => [
                    ['name' => 'applicant_name',   'label' => 'Your / Group Full Name',      'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Eldoret Youth Empowerment Club / Mercy Jepkemoi'],
                    ['name' => 'contact_details',  'label' => 'Contact Details',             'type' => 'text',     'required' => true,  'placeholder' => 'Phone: 07XXXXXXXX | Email: info@domain.com'],
                    ['name' => 'date',             'label' => 'Date',                        'type' => 'date',     'required' => true],
                    ['name' => 'sponsor_name',     'label' => 'Sponsor / Company Name',      'type' => 'text',     'required' => true,  'placeholder' => 'e.g. KCB Foundation'],
                    ['name' => 'sponsor_address',  'label' => 'Sponsor Address',             'type' => 'text',     'required' => true,  'placeholder' => 'Kencom House, Nairobi'],
                    ['name' => 'project_title',    'label' => 'Project or Event Name',       'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Community Tech Skills Boot Camp 2026'],
                    ['name' => 'support_details',  'label' => 'Support Requested & Purpose', 'type' => 'textarea', 'required' => true,  'placeholder' => 'Specify funding amount or equipment required and how it empowers the beneficiaries.'],
                ],
                'definition'  => [
                    'title' => 'Sponsorship Request Letter',
                    'sections' => [
                        [
                            'type'    => 'header',
                            'content' => "{{applicant_name}}\n{{contact_details}}\nDate: {{date}}",
                        ],
                        [
                            'type'    => 'recipient',
                            'content' => "{{sponsor_name}}\n{{sponsor_address}}",
                        ],
                        [
                            'type'    => 'subject',
                            'content' => "RE: REQUEST FOR SPONSORSHIP SUPPORT FOR {{project_title}}",
                        ],
                        [
                            'type'    => 'salutation',
                            'content' => "Dear Sir/Madam,",
                        ],
                        [
                            'type'    => 'body',
                            'content' => "We have the honour to present this request for sponsorship to your esteemed organization for {{project_title}}.\n\n{{support_details}}\n\nPartnering with {{sponsor_name}} on this initiative will yield impactful social transformation and visibility. We welcome an opportunity to present a detailed project brief and discuss partnership synergies at your earliest convenience.\n\nThank you for your consideration and ongoing commitment to community empowerment.",
                        ],
                        [
                            'type'    => 'closing',
                            'content' => "Yours faithfully,\n\n\n_________________________________\n{{applicant_name}}",
                        ],
                    ],
                ],
            ],

            // =========================================================================
            // AFFIDAVITS
            // =========================================================================
            [
                'category_id' => $affidavitsCat->id,
                'name'        => 'Affidavit of Lost National ID / Passport',
                'slug'        => 'affidavit-lost-id',
                'description' => 'Sworn statement confirming loss of National Identity Card or Passport for Police Abstract and NRB replacement.',
                'price'       => 100.00,
                'sort_order'  => 5,
                'is_active'   => true,
                'schema'      => [
                    ['name' => 'deponent_name',  'label' => 'Your Full Name (Deponent)',   'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Dennis Kiprono Korir'],
                    ['name' => 'lost_doc_type',  'label' => 'Lost Document Type',          'type' => 'select',   'required' => true,  'options' => ['National Identity Card (ID)', 'Kenyan Passport', 'Alien Identity Card', 'Driving Licence']],
                    ['name' => 'doc_number',     'label' => 'Lost Document Number',        'type' => 'text',     'required' => true,  'placeholder' => 'e.g. ID No. 34567890 or Passport No. AK123456'],
                    ['name' => 'date_of_loss',   'label' => 'Approximate Date of Loss',    'type' => 'text',     'required' => true,  'placeholder' => 'e.g. On or about 15th September 2026'],
                    ['name' => 'place_of_loss',  'label' => 'Place / Circumstances of Loss', 'type' => 'textarea', 'required' => true, 'placeholder' => 'e.g. Lost in transit between Nairobi CBD and Westlands. Diligent searches have yielded no recovery.'],
                    ['name' => 'police_station', 'label' => 'Police Station Reported To',  'type' => 'text',     'required' => false, 'placeholder' => 'e.g. Central Police Station, Nairobi'],
                ],
                'definition'  => [
                    'title' => 'AFFIDAVIT OF LOST IDENTIFICATION DOCUMENT',
                    'sections' => [
                        [
                            'type'    => 'preamble',
                            'content' => "REPUBLIC OF KENYA\nIN THE MATTER OF THE OATHS AND STATUTORY DECLARATIONS ACT (CAP 15, LAWS OF KENYA)\n\nI, {{deponent_name}}, a male/female adult of sound mind, residing in the Republic of Kenya, do solemnly swear and state as follows:",
                        ],
                        [
                            'type'    => 'body',
                            'content' => "1. THAT I am the lawful and bona fide holder of {{lost_doc_type}} registered under Document Number {{doc_number}}.\n\n2. THAT on or about {{date_of_loss}}, I unfortunately lost the said {{lost_doc_type}} around {{place_of_loss}}.\n\n3. THAT despite extensive and diligent efforts to search for and trace the said identification document, the same has not been recovered.\n\n4. THAT the said document has not been pledged, mortgaged, deposited as security, or transferred to any third party.\n\n5. THAT I have reported the loss to {{police_station}} and this affidavit is sworn to facilitate the official replacement and re-issuance of the said document by the National Registration Bureau / Department of Immigration Services.\n\n6. THAT whatever is stated hereinabove is true to the best of my knowledge, information, and belief.",
                        ],
                        [
                            'type'    => 'jurat',
                            'content' => "SWORN by the said {{deponent_name}} at _________________________\nThis ________ day of ________________________ 20_____\n\nBEFORE ME:\n\n___________________________________\nCOMMISSIONER FOR OATHS\n\n\n___________________________________\nDEPONENT: {{deponent_name}}",
                        ],
                    ],
                ],
            ],

            [
                'category_id' => $affidavitsCat->id,
                'name'        => 'Affidavit of Financial Support',
                'slug'        => 'affidavit-support',
                'description' => 'Sworn guarantee by sponsor to support student, family member, or dependent financially.',
                'price'       => 100.00,
                'sort_order'  => 6,
                'is_active'   => true,
                'schema'      => [
                    ['name' => 'sponsor_name',     'label' => 'Sponsor Full Name (Deponent)', 'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Samuel Kiptoo Langat'],
                    ['name' => 'sponsor_id',       'label' => 'Sponsor National ID / Passport', 'type' => 'text',  'required' => true,  'placeholder' => 'e.g. 21987456'],
                    ['name' => 'sponsor_income',   'label' => 'Sponsor Occupation & Income Source', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. Businessman / Managing Director at Apex Agro Ltd'],
                    ['name' => 'beneficiary_name', 'label' => 'Beneficiary Full Name',        'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Joy Chebet Langat'],
                    ['name' => 'relationship',     'label' => 'Relationship to Beneficiary',  'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Biological Daughter / Ward'],
                    ['name' => 'support_purpose',  'label' => 'Purpose of Support',           'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Tuition and living expenses at Kenyatta University / Overseas Education'],
                ],
                'definition'  => [
                    'title' => 'AFFIDAVIT OF FINANCIAL SPONSORSHIP AND SUPPORT',
                    'sections' => [
                        [
                            'type'    => 'preamble',
                            'content' => "REPUBLIC OF KENYA\nIN THE MATTER OF THE OATHS AND STATUTORY DECLARATIONS ACT (CAP 15, LAWS OF KENYA)\n\nI, {{sponsor_name}}, holder of National ID / Passport No. {{sponsor_id}}, a resident citizen of the Republic of Kenya, do solemnly swear and declare as follows:",
                        ],
                        [
                            'type'    => 'body',
                            'content' => "1. THAT I am an adult of sound mind and the {{relationship}} of {{beneficiary_name}}.\n\n2. THAT I am gainfully engaged as {{sponsor_income}} with sufficient financial capability and liquid resources to undertake full sponsorship obligations.\n\n3. THAT I undertake to personally and unconditionally fund and cover all financial expenses, including tuition, accommodation, health insurance, and general living costs for {{beneficiary_name}} in respect of {{support_purpose}}.\n\n4. THAT the beneficiary will not become a public charge, burden, or destitute during the tenure of my sponsorship commitment.\n\n5. THAT I depone to this affidavit to verify my financial commitment to all relevant university boards, immigration authorities, and financial institutions.\n\n6. THAT what is stated herein is true and correct to the best of my knowledge, information, and belief.",
                        ],
                        [
                            'type'    => 'jurat',
                            'content' => "SWORN by the said {{sponsor_name}} at _________________________\nThis ________ day of ________________________ 20_____\n\nBEFORE ME:\n\n___________________________________\nCOMMISSIONER FOR OATHS\n\n\n___________________________________\nDEPONENT: {{sponsor_name}}",
                        ],
                    ],
                ],
            ],

            [
                'category_id' => $affidavitsCat->id,
                'name'        => 'Affidavit of Single Status (Bachelorhood / Spinsterhood)',
                'slug'        => 'affidavit-single-status',
                'description' => 'Sworn affirmation confirming unmarried status required by the Registrar of Marriages and foreign embassies.',
                'price'       => 100.00,
                'sort_order'  => 7,
                'is_active'   => true,
                'schema'      => [
                    ['name' => 'deponent_name',  'label' => 'Your Full Name',              'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Cynthia Mwende Mutisya'],
                    ['name' => 'id_number',      'label' => 'National ID / Passport No.',  'type' => 'text',     'required' => true,  'placeholder' => 'e.g. 31245678'],
                    ['name' => 'residence',      'label' => 'Place of Ordinary Residence', 'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Machakos County / South B, Nairobi'],
                    ['name' => 'purpose',        'label' => 'Purpose (e.g. Civil Marriage Registration / Visa Application)', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. Official marriage notice at the Office of the Attorney General'],
                ],
                'definition'  => [
                    'title' => 'AFFIDAVIT OF SINGLE MARITAL STATUS',
                    'sections' => [
                        [
                            'type'    => 'preamble',
                            'content' => "REPUBLIC OF KENYA\nIN THE MATTER OF THE OATHS AND STATUTORY DECLARATIONS ACT (CAP 15, LAWS OF KENYA)\nAND IN THE MATTER OF THE MARRIAGE ACT (NO. 4 OF 2014)\n\nI, {{deponent_name}}, holder of National ID / Passport No. {{id_number}}, residing at {{residence}}, do hereby make oath and solemnly state as follows:",
                        ],
                        [
                            'type'    => 'body',
                            'content' => "1. THAT I am an adult of full age and sound mind, competent to swear this statutory declaration.\n\n2. THAT I have never contracted any form of marriage—whether civil, customary, Christian, Hindu, or Islamic—and I am legally a bachelor / spinster.\n\n3. THAT there exists no legal impediment, existing marital bond, or barrier preventing me from entering into a lawful marriage under the laws of Kenya or international conventions.\n\n4. THAT this affidavit is made in good faith for {{purpose}}.\n\n5. THAT whatever is deponed herein is true to the best of my knowledge, information, and belief.",
                        ],
                        [
                            'type'    => 'jurat',
                            'content' => "SWORN by the said {{deponent_name}} at _________________________\nThis ________ day of ________________________ 20_____\n\nBEFORE ME:\n\n___________________________________\nCOMMISSIONER FOR OATHS\n\n\n___________________________________\nDEPONENT: {{deponent_name}}",
                        ],
                    ],
                ],
            ],

            [
                'category_id' => $affidavitsCat->id,
                'name'        => 'Parental Consent Affidavit for Minor Travel',
                'slug'        => 'affidavit-child-travel',
                'description' => 'Sworn parental declaration authorizing a minor child to travel abroad or domestically.',
                'price'       => 100.00,
                'sort_order'  => 8,
                'is_active'   => true,
                'schema'      => [
                    ['name' => 'parent_name',       'label' => 'Parent / Guardian Full Name', 'type' => 'text', 'required' => true,  'placeholder' => 'e.g. Joshua Omondi Aketch'],
                    ['name' => 'parent_id',         'label' => 'Parent National ID / Passport', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. 24512398'],
                    ['name' => 'child_name',        'label' => 'Child Full Name',             'type' => 'text', 'required' => true,  'placeholder' => 'e.g. Liam Baraka Omondi'],
                    ['name' => 'child_birth_cert',  'label' => 'Child Birth Certificate / Passport No.', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. Birth Cert Entry No. 567890'],
                    ['name' => 'destination',       'label' => 'Destination Country / Town',  'type' => 'text', 'required' => true,  'placeholder' => 'e.g. United Kingdom / Mombasa, Kenya'],
                    ['name' => 'travel_dates',      'label' => 'Travel Period / Dates',       'type' => 'text', 'required' => true,  'placeholder' => 'e.g. 1st December 2026 to 15th January 2027'],
                    ['name' => 'accompanying_adult','label' => 'Accompanying Adult / Teacher / Guardian', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. Travelling with Mother: Sarah Achieng / School delegation'],
                ],
                'definition'  => [
                    'title' => 'PARENTAL CONSENT AFFIDAVIT FOR MINOR CHILD TRAVEL',
                    'sections' => [
                        [
                            'type'    => 'preamble',
                            'content' => "REPUBLIC OF KENYA\nIN THE MATTER OF THE OATHS AND STATUTORY DECLARATIONS ACT (CAP 15, LAWS OF KENYA)\n\nI, {{parent_name}}, holder of National ID / Passport No. {{parent_id}}, residing in Kenya, do solemnly swear and declare as follows:",
                        ],
                        [
                            'type'    => 'body',
                            'content' => "1. THAT I am the biological parent / lawful legal guardian of minor child {{child_name}}, holder of Birth Certificate / Passport No. {{child_birth_cert}}.\n\n2. THAT I hereby grant my full, informed, and unconditional consent for the said minor to travel to {{destination}} during the period of {{travel_dates}}.\n\n3. THAT the said minor will be traveling in the care and custody of {{accompanying_adult}}.\n\n4. THAT I authorize the accompanying adult to make any necessary welfare, travel, and emergency medical decisions on behalf of my child during this trip.\n\n5. THAT this affidavit is executed for presentation to the Department of Immigration Services, airline authorities, and border control officials.",
                        ],
                        [
                            'type'    => 'jurat',
                            'content' => "SWORN by the said {{parent_name}} at _________________________\nThis ________ day of ________________________ 20_____\n\nBEFORE ME:\n\n___________________________________\nCOMMISSIONER FOR OATHS\n\n\n___________________________________\nDEPONENT (PARENT/GUARDIAN): {{parent_name}}",
                        ],
                    ],
                ],
            ],
        ];

        foreach ($templates as $t) {
            Template::updateOrCreate(['slug' => $t['slug']], $t);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $slugs = [
            'resignation-letter',
            'complaint-letter',
            'leave-application',
            'demand-letter',
            'sponsorship-request',
            'affidavit-lost-id',
            'affidavit-support',
            'affidavit-single-status',
            'affidavit-child-travel',
        ];

        Template::whereIn('slug', $slugs)->delete();
    }
};
