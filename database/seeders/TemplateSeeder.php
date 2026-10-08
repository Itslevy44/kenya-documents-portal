<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Seed 15 document templates with full schema and definition JSON.
     */
    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');

        $templates = [

            // ─── 1. Application Letter ───────────────────────────────────────────
            [
                'category_slug' => 'letters',
                'name'          => 'Application Letter',
                'slug'          => 'application-letter',
                'description'   => 'A formal application letter for a job or position.',
                'price'         => '50.00',
                'sort_order'    => 1,
                'schema'        => [
                    ['name' => 'applicant_name',   'label' => 'Your Full Name',          'type' => 'text',     'required' => true,  'placeholder' => 'e.g. John Kamau Mwangi'],
                    ['name' => 'applicant_address', 'label' => 'Your Address',            'type' => 'textarea', 'required' => true,  'placeholder' => 'P.O. Box 1234, Nairobi'],
                    ['name' => 'applicant_phone',   'label' => 'Your Phone Number',       'type' => 'phone',    'required' => true,  'placeholder' => '07XXXXXXXX'],
                    ['name' => 'applicant_email',   'label' => 'Your Email Address',      'type' => 'email',    'required' => false, 'placeholder' => 'yourname@email.com'],
                    ['name' => 'date',              'label' => 'Date',                    'type' => 'date',     'required' => true],
                    ['name' => 'recipient_name',    'label' => 'Recipient Name/Title',    'type' => 'text',     'required' => true,  'placeholder' => 'e.g. The Human Resources Manager'],
                    ['name' => 'company_name',      'label' => 'Company / Organisation',  'type' => 'text',     'required' => true,  'placeholder' => 'e.g. ABC Company Ltd'],
                    ['name' => 'company_address',   'label' => 'Company Address',         'type' => 'textarea', 'required' => true,  'placeholder' => 'P.O. Box 5678, Nairobi'],
                    ['name' => 'position_applied',  'label' => 'Position Applied For',    'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Accountant'],
                    ['name' => 'qualifications',    'label' => 'Your Qualifications',     'type' => 'textarea', 'required' => true,  'placeholder' => 'List your relevant qualifications and experience', 'help' => 'Briefly describe your education and work experience.'],
                    ['name' => 'referee_name',      'label' => 'Referee Name',            'type' => 'text',     'required' => false, 'placeholder' => 'e.g. Dr. Alice Wanjiru'],
                    ['name' => 'referee_contact',   'label' => 'Referee Contact',         'type' => 'text',     'required' => false, 'placeholder' => 'Phone or email'],
                ],
                'definition' => [
                    'title' => 'Application Letter',
                    'sections' => [
                        [
                            'heading'       => null,
                            'body_template' => "{{applicant_name}}\n{{applicant_address}}\nTel: {{applicant_phone}}\nEmail: {{applicant_email}}\n\n{{date}}",
                        ],
                        [
                            'heading'       => null,
                            'body_template' => "{{recipient_name}}\n{{company_name}}\n{{company_address}}",
                        ],
                        [
                            'heading'       => 'Dear Sir/Madam,',
                            'body_template' => "RE: APPLICATION FOR THE POSITION OF {{position_applied}}\n\nI write to express my sincere interest in the above-mentioned position as advertised. I am confident that my qualifications and experience make me a suitable candidate for this role.\n\n{{qualifications}}\n\nI am a hardworking, dedicated, and results-oriented individual. I am eager to contribute positively to {{company_name}} and to grow professionally in this role.\n\nEnclosed herewith are my curriculum vitae and copies of relevant certificates for your consideration. I am available for an interview at your earliest convenience.\n\nYours faithfully,\n\n\n{{applicant_name}}\nTel: {{applicant_phone}}",
                        ],
                        [
                            'heading'       => 'References',
                            'body_template' => "{{referee_name}}\n{{referee_contact}}",
                        ],
                    ],
                ],
            ],

            // ─── 2. Recommendation Letter ────────────────────────────────────────
            [
                'category_slug' => 'letters',
                'name'          => 'Recommendation Letter',
                'slug'          => 'recommendation-letter',
                'description'   => 'A formal recommendation letter from an employer, teacher, or referee.',
                'price'         => '50.00',
                'sort_order'    => 2,
                'schema'        => [
                    ['name' => 'author_name',      'label' => 'Your (Author) Full Name',      'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Dr. Grace Njoroge'],
                    ['name' => 'author_title',     'label' => 'Your Title / Designation',     'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Principal, Nairobi High School'],
                    ['name' => 'author_contact',   'label' => 'Your Contact (Phone/Email)',   'type' => 'text',     'required' => true,  'placeholder' => 'e.g. 0712345678'],
                    ['name' => 'date',             'label' => 'Date',                         'type' => 'date',     'required' => true],
                    ['name' => 'candidate_name',   'label' => 'Candidate Full Name',          'type' => 'text',     'required' => true,  'placeholder' => 'e.g. James Otieno'],
                    ['name' => 'candidate_gender', 'label' => 'Candidate Gender',             'type' => 'select',   'required' => true,  'options' => ['He/Him', 'She/Her', 'They/Them']],
                    ['name' => 'relationship',     'label' => 'Your Relationship to Candidate', 'type' => 'text',  'required' => true,  'placeholder' => 'e.g. former employer, class teacher'],
                    ['name' => 'duration',         'label' => 'Duration Known',               'type' => 'text',     'required' => true,  'placeholder' => 'e.g. 3 years'],
                    ['name' => 'qualities',        'label' => 'Candidate Qualities & Achievements', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Describe the candidate\'s key strengths and achievements'],
                    ['name' => 'purpose',          'label' => 'Purpose of Recommendation',   'type' => 'text',     'required' => true,  'placeholder' => 'e.g. university admission, employment'],
                ],
                'definition' => [
                    'title' => 'Letter of Recommendation',
                    'sections' => [
                        [
                            'heading'       => null,
                            'body_template' => "{{author_name}}\n{{author_title}}\nContact: {{author_contact}}\n\n{{date}}",
                        ],
                        [
                            'heading'       => 'TO WHOM IT MAY CONCERN',
                            'body_template' => "RE: RECOMMENDATION FOR {{candidate_name}}\n\nI am pleased to write this letter of recommendation for {{candidate_name}}, whom I have known as {{relationship}} for {{duration}}.\n\n{{qualities}}\n\nI recommend {{candidate_name}} without reservation for {{purpose}}. {{candidate_gender}} will be an asset to any team or institution.\n\nShould you require any further information, please do not hesitate to contact me.\n\nYours sincerely,\n\n\n{{author_name}}\n{{author_title}}\n{{author_contact}}",
                        ],
                    ],
                ],
            ],

            // ─── 3. School Fee Request Letter ────────────────────────────────────
            [
                'category_slug' => 'school',
                'name'          => 'School Fee Request Letter',
                'slug'          => 'school-fee-request',
                'description'   => 'A letter requesting school fee payment extension or waiver.',
                'price'         => '50.00',
                'sort_order'    => 3,
                'schema'        => [
                    ['name' => 'parent_name',      'label' => 'Parent/Guardian Full Name', 'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Mary Wambui Kamau'],
                    ['name' => 'parent_address',   'label' => 'Address',                  'type' => 'textarea', 'required' => true,  'placeholder' => 'P.O. Box, Location'],
                    ['name' => 'parent_phone',     'label' => 'Phone Number',             'type' => 'phone',    'required' => true,  'placeholder' => '07XXXXXXXX'],
                    ['name' => 'date',             'label' => 'Date',                     'type' => 'date',     'required' => true],
                    ['name' => 'principal_name',   'label' => 'Principal\'s Name',        'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Mr. Samuel Kariuki'],
                    ['name' => 'school_name',      'label' => 'School Name',              'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Mwangaza Secondary School'],
                    ['name' => 'student_name',     'label' => 'Student Full Name',        'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Peter Njoroge Kamau'],
                    ['name' => 'class_form',       'label' => 'Class / Form',             'type' => 'text',     'required' => true,  'placeholder' => 'e.g. Form 3 East'],
                    ['name' => 'amount_owed',      'label' => 'Amount Owed (KSh)',        'type' => 'number',   'required' => true,  'placeholder' => 'e.g. 15000'],
                    ['name' => 'reason',           'label' => 'Reason for Request',       'type' => 'textarea', 'required' => true,  'placeholder' => 'Explain the financial hardship or reason for the request'],
                    ['name' => 'payment_date',     'label' => 'Proposed Payment Date',    'type' => 'date',     'required' => true,  'help' => 'The date by which you promise to pay the full amount.'],
                ],
                'definition' => [
                    'title' => 'School Fee Request Letter',
                    'sections' => [
                        [
                            'heading'       => null,
                            'body_template' => "{{parent_name}}\n{{parent_address}}\nTel: {{parent_phone}}\n\n{{date}}",
                        ],
                        [
                            'heading'       => null,
                            'body_template' => "{{principal_name}}\nThe Principal\n{{school_name}}",
                        ],
                        [
                            'heading'       => 'Dear {{principal_name}},',
                            'body_template' => "RE: REQUEST FOR SCHOOL FEE EXTENSION — {{student_name}}, {{class_form}}\n\nI am the parent/guardian of {{student_name}}, a student in {{class_form}} at your esteemed institution. I am writing to kindly request for a fee payment extension regarding the outstanding balance of KSh {{amount_owed}}.\n\n{{reason}}\n\nI humbly request that you allow my child to continue attending classes while I make arrangements to clear the balance. I commit to settling the outstanding fee in full by {{payment_date}}.\n\nI assure you of my co-operation and thank you for your understanding and consideration.\n\nYours faithfully,\n\n\n{{parent_name}}\nParent/Guardian of {{student_name}}\nTel: {{parent_phone}}",
                        ],
                    ],
                ],
            ],

            // ─── 4. Consent Letter ───────────────────────────────────────────────
            [
                'category_slug' => 'letters',
                'name'          => 'Consent Letter',
                'slug'          => 'consent-letter',
                'description'   => 'A parental or guardian consent letter for travel, medical, or school activities.',
                'price'         => '50.00',
                'sort_order'    => 4,
                'schema'        => [
                    ['name' => 'guardian_name',    'label' => 'Guardian/Parent Full Name',  'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Joseph Maina'],
                    ['name' => 'guardian_id',      'label' => 'Guardian ID Number',         'type' => 'text',    'required' => true,  'placeholder' => 'e.g. 12345678'],
                    ['name' => 'guardian_phone',   'label' => 'Guardian Phone',             'type' => 'phone',   'required' => true,  'placeholder' => '07XXXXXXXX'],
                    ['name' => 'date',             'label' => 'Date',                       'type' => 'date',    'required' => true],
                    ['name' => 'child_name',       'label' => 'Child/Beneficiary Full Name','type' => 'text',    'required' => true,  'placeholder' => 'e.g. Sarah Wanjiku Maina'],
                    ['name' => 'child_dob',        'label' => 'Child Date of Birth',        'type' => 'date',    'required' => true],
                    ['name' => 'consent_for',      'label' => 'Consent Granted For',        'type' => 'text',    'required' => true,  'placeholder' => 'e.g. travel to Uganda, medical procedure'],
                    ['name' => 'activity_date',    'label' => 'Activity / Travel Date',     'type' => 'date',    'required' => true],
                    ['name' => 'authorised_person', 'label' => 'Authorised Person (if any)', 'type' => 'text',  'required' => false, 'placeholder' => 'Person accompanying the child'],
                    ['name' => 'additional_notes', 'label' => 'Additional Notes',           'type' => 'textarea','required' => false, 'placeholder' => 'Any additional conditions or instructions'],
                ],
                'definition' => [
                    'title' => 'Parental / Guardian Consent Letter',
                    'sections' => [
                        [
                            'heading'       => null,
                            'body_template' => "{{guardian_name}}\nID No: {{guardian_id}}\nTel: {{guardian_phone}}\n\n{{date}}",
                        ],
                        [
                            'heading'       => 'TO WHOM IT MAY CONCERN',
                            'body_template' => "I, {{guardian_name}}, holder of National ID No. {{guardian_id}}, hereby give my full consent for {{child_name}} (Date of Birth: {{child_dob}}) to participate in the following activity: {{consent_for}}, scheduled for {{activity_date}}.\n\n{{additional_notes}}\n\nI confirm that I am the lawful parent/guardian of the said child and that I grant this consent freely and voluntarily.\n\nI authorise {{authorised_person}} to act on my behalf as necessary.\n\nSigned:\n\n\n{{guardian_name}}\nID No: {{guardian_id}}\nDate: {{date}}",
                        ],
                    ],
                ],
            ],

            // ─── 5. Invitation Letter ────────────────────────────────────────────
            [
                'category_slug' => 'letters',
                'name'          => 'Invitation Letter',
                'slug'          => 'invitation-letter',
                'description'   => 'A formal invitation letter for an event, meeting, or official function.',
                'price'         => '50.00',
                'sort_order'    => 5,
                'schema'        => [
                    ['name' => 'sender_name',      'label' => 'Sender Full Name / Organisation', 'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Nairobi Youth Forum'],
                    ['name' => 'sender_address',   'label' => 'Sender Address',                  'type' => 'textarea','required' => true,  'placeholder' => 'P.O. Box, Location'],
                    ['name' => 'sender_phone',     'label' => 'Sender Phone',                    'type' => 'phone',   'required' => true,  'placeholder' => '07XXXXXXXX'],
                    ['name' => 'date',             'label' => 'Letter Date',                     'type' => 'date',    'required' => true],
                    ['name' => 'recipient_name',   'label' => 'Recipient Name',                  'type' => 'text',    'required' => true,  'placeholder' => 'e.g. The Chief Guest'],
                    ['name' => 'event_name',       'label' => 'Event Name',                      'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Annual General Meeting 2025'],
                    ['name' => 'event_date',       'label' => 'Event Date',                      'type' => 'date',    'required' => true],
                    ['name' => 'event_time',       'label' => 'Event Time',                      'type' => 'text',    'required' => true,  'placeholder' => 'e.g. 9:00 AM – 1:00 PM'],
                    ['name' => 'event_venue',      'label' => 'Event Venue',                     'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Kenyatta International Convention Centre, Nairobi'],
                    ['name' => 'event_details',    'label' => 'Event Description',               'type' => 'textarea','required' => true,  'placeholder' => 'Briefly describe the event and its purpose'],
                    ['name' => 'rsvp_contact',     'label' => 'RSVP Contact',                    'type' => 'text',    'required' => false, 'placeholder' => 'Phone or email for RSVP'],
                ],
                'definition' => [
                    'title' => 'Invitation Letter',
                    'sections' => [
                        [
                            'heading'       => null,
                            'body_template' => "{{sender_name}}\n{{sender_address}}\nTel: {{sender_phone}}\n\n{{date}}",
                        ],
                        [
                            'heading'       => null,
                            'body_template' => "{{recipient_name}}",
                        ],
                        [
                            'heading'       => 'Dear {{recipient_name}},',
                            'body_template' => "RE: INVITATION TO {{event_name}}\n\nOn behalf of {{sender_name}}, I am delighted to invite you to {{event_name}}.\n\n{{event_details}}\n\nEvent Details:\nDate: {{event_date}}\nTime: {{event_time}}\nVenue: {{event_venue}}\n\nYour presence will greatly honour us. Kindly confirm your attendance by contacting us at {{rsvp_contact}}.\n\nWe look forward to welcoming you.\n\nYours sincerely,\n\n\n{{sender_name}}\nTel: {{sender_phone}}",
                        ],
                    ],
                ],
            ],

            // ─── 6. Affidavit – Lost Certificate ─────────────────────────────────
            [
                'category_slug' => 'affidavits',
                'name'          => 'Affidavit of Lost Certificate',
                'slug'          => 'affidavit-lost-certificate',
                'description'   => 'A sworn affidavit declaring that an academic or official certificate has been lost.',
                'price'         => '100.00',
                'sort_order'    => 6,
                'schema'        => [
                    ['name' => 'deponent_name',      'label' => 'Your Full Name',             'type' => 'text',    'required' => true,  'placeholder' => 'e.g. David Mwenda Mutua'],
                    ['name' => 'deponent_id',        'label' => 'National ID Number',         'type' => 'text',    'required' => true,  'placeholder' => 'e.g. 23456789'],
                    ['name' => 'deponent_address',   'label' => 'Residential Address',        'type' => 'textarea','required' => true,  'placeholder' => 'Village/Estate, Location, County'],
                    ['name' => 'deponent_phone',     'label' => 'Phone Number',               'type' => 'phone',   'required' => true,  'placeholder' => '07XXXXXXXX'],
                    ['name' => 'certificate_type',   'label' => 'Type of Certificate Lost',   'type' => 'text',    'required' => true,  'placeholder' => 'e.g. KCSE Certificate, Degree Certificate'],
                    ['name' => 'certificate_year',   'label' => 'Year of Certificate',        'type' => 'text',    'required' => true,  'placeholder' => 'e.g. 2018'],
                    ['name' => 'institution_name',   'label' => 'Issuing Institution',        'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Kenya National Examinations Council'],
                    ['name' => 'index_reg_number',   'label' => 'Index/Registration Number',  'type' => 'text',    'required' => true,  'placeholder' => 'e.g. 12345678/2018'],
                    ['name' => 'loss_circumstances', 'label' => 'Circumstances of Loss',      'type' => 'textarea','required' => true,  'placeholder' => 'Describe when and how the certificate was lost'],
                    ['name' => 'date',               'label' => 'Date of Swearing',           'type' => 'date',    'required' => true],
                ],
                'definition' => [
                    'title' => 'Affidavit of Lost Certificate',
                    'sections' => [
                        [
                            'heading'       => 'REPUBLIC OF KENYA',
                            'body_template' => "IN THE MATTER OF THE OATHS AND STATUTORY DECLARATIONS ACT (Cap. 15)\n\nAFFIDAVIT",
                        ],
                        [
                            'heading'       => null,
                            'body_template' => "I, {{deponent_name}}, holder of National ID No. {{deponent_id}}, of P.O. Box / Address: {{deponent_address}}, phone number {{deponent_phone}}, do hereby make oath and solemnly declare as follows:",
                        ],
                        [
                            'heading'       => null,
                            'body_template' => "1. THAT I am the lawful holder of a {{certificate_type}} issued by {{institution_name}} in the year {{certificate_year}} with Index/Registration Number {{index_reg_number}}.\n\n2. THAT the said certificate has been lost and cannot be found despite diligent search.\n\n3. THAT the circumstances leading to the loss of the said certificate are as follows: {{loss_circumstances}}\n\n4. THAT I am making this affidavit to enable me to apply for a replacement certificate and for all other lawful purposes.\n\n5. THAT the contents of this affidavit are true to the best of my knowledge and belief.",
                        ],
                        [
                            'heading'       => null,
                            'body_template' => "SWORN at __________________ this {{date}}\n\nDEPONENT: _______________________________\n{{deponent_name}}\nID No: {{deponent_id}}\n\nBEFORE ME:\n\n_______________________________\nCOMMISSIONER FOR OATHS / MAGISTRATE\nStamp & Signature",
                        ],
                    ],
                ],
            ],

            // ─── 7. Affidavit – Name Change ───────────────────────────────────────
            [
                'category_slug' => 'affidavits',
                'name'          => 'Affidavit of Name Change',
                'slug'          => 'affidavit-name-change',
                'description'   => 'A sworn affidavit declaring a change of name.',
                'price'         => '100.00',
                'sort_order'    => 7,
                'schema'        => [
                    ['name' => 'deponent_id',      'label' => 'National ID Number',          'type' => 'text',    'required' => true,  'placeholder' => 'e.g. 34567890'],
                    ['name' => 'old_name',         'label' => 'Former Full Name',             'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Jane Akinyi Were'],
                    ['name' => 'new_name',         'label' => 'New Full Name',                'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Jane Akinyi Odhiambo'],
                    ['name' => 'deponent_address', 'label' => 'Residential Address',          'type' => 'textarea','required' => true,  'placeholder' => 'Village/Estate, Location, County'],
                    ['name' => 'reason_for_change','label' => 'Reason for Name Change',       'type' => 'text',    'required' => true,  'placeholder' => 'e.g. marriage, preference, error correction', 'help' => 'State the reason concisely.'],
                    ['name' => 'date',             'label' => 'Date of Swearing',             'type' => 'date',    'required' => true],
                ],
                'definition' => [
                    'title' => 'Affidavit of Name Change',
                    'sections' => [
                        [
                            'heading'       => 'REPUBLIC OF KENYA',
                            'body_template' => "IN THE MATTER OF THE OATHS AND STATUTORY DECLARATIONS ACT (Cap. 15)\n\nAFFIDAVIT",
                        ],
                        [
                            'heading'       => null,
                            'body_template' => "I, {{old_name}}, holder of National ID No. {{deponent_id}}, of {{deponent_address}}, do hereby make oath and solemnly declare as follows:",
                        ],
                        [
                            'heading'       => null,
                            'body_template' => "1. THAT I was formerly known as {{old_name}} and that I now wish to be known as {{new_name}} for all purposes.\n\n2. THAT the reason for this change of name is: {{reason_for_change}}.\n\n3. THAT I renounce and abandon the use of my former name {{old_name}} and adopt in its place the name {{new_name}}.\n\n4. THAT I authorise and request all persons and organisations to use my new name {{new_name}} in all documents, records, and dealings relating to me.\n\n5. THAT the contents of this affidavit are true to the best of my knowledge and belief.",
                        ],
                        [
                            'heading'       => null,
                            'body_template' => "SWORN at __________________ this {{date}}\n\nDEPONENT: _______________________________\n{{new_name}} (formerly {{old_name}})\nID No: {{deponent_id}}\n\nBEFORE ME:\n\n_______________________________\nCOMMISSIONER FOR OATHS / MAGISTRATE\nStamp & Signature",
                        ],
                    ],
                ],
            ],

            // ─── 8. Affidavit – Proof of Residence ───────────────────────────────
            [
                'category_slug' => 'affidavits',
                'name'          => 'Affidavit of Proof of Residence',
                'slug'          => 'affidavit-residence',
                'description'   => 'A sworn affidavit confirming the place of residence of the deponent.',
                'price'         => '100.00',
                'sort_order'    => 8,
                'schema'        => [
                    ['name' => 'deponent_name',    'label' => 'Your Full Name',          'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Francis Njoroge Gitau'],
                    ['name' => 'deponent_id',      'label' => 'National ID Number',      'type' => 'text',    'required' => true,  'placeholder' => 'e.g. 34567891'],
                    ['name' => 'village_estate',   'label' => 'Village / Estate',        'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Kihara Village'],
                    ['name' => 'location',         'label' => 'Location / Sub-location', 'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Kihara Location, Kiambu Sub-county'],
                    ['name' => 'county',           'label' => 'County',                  'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Kiambu County'],
                    ['name' => 'years_resident',   'label' => 'Years Living There',      'type' => 'number',  'required' => true,  'placeholder' => 'e.g. 5'],
                    ['name' => 'purpose',          'label' => 'Purpose of Affidavit',    'type' => 'text',    'required' => true,  'placeholder' => 'e.g. bank account opening, land registration'],
                    ['name' => 'date',             'label' => 'Date of Swearing',        'type' => 'date',    'required' => true],
                ],
                'definition' => [
                    'title' => 'Affidavit of Proof of Residence',
                    'sections' => [
                        [
                            'heading'       => 'REPUBLIC OF KENYA',
                            'body_template' => "IN THE MATTER OF THE OATHS AND STATUTORY DECLARATIONS ACT (Cap. 15)\n\nAFFIDAVIT OF PROOF OF RESIDENCE",
                        ],
                        [
                            'heading'       => null,
                            'body_template' => "I, {{deponent_name}}, holder of National ID No. {{deponent_id}}, do hereby make oath and solemnly declare as follows:",
                        ],
                        [
                            'heading'       => null,
                            'body_template' => "1. THAT I am a resident of {{village_estate}}, {{location}}, {{county}}.\n\n2. THAT I have been residing at the above address for a period of {{years_resident}} years.\n\n3. THAT I am making this affidavit for the purpose of {{purpose}} and for all other lawful purposes.\n\n4. THAT the contents of this affidavit are true to the best of my knowledge and belief.",
                        ],
                        [
                            'heading'       => null,
                            'body_template' => "SWORN at __________________ this {{date}}\n\nDEPONENT: _______________________________\n{{deponent_name}}\nID No: {{deponent_id}}\n\nBEFORE ME:\n\n_______________________________\nCOMMISSIONER FOR OATHS / MAGISTRATE\nStamp & Signature",
                        ],
                    ],
                ],
            ],

            // ─── 9. General Purpose Affidavit ────────────────────────────────────
            [
                'category_slug' => 'affidavits',
                'name'          => 'General Purpose Affidavit',
                'slug'          => 'affidavit-general',
                'description'   => 'A general-purpose sworn affidavit for any formal declaration.',
                'price'         => '100.00',
                'sort_order'    => 9,
                'schema'        => [
                    ['name' => 'deponent_name',    'label' => 'Your Full Name',       'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Alice Nyambura Wanjiku'],
                    ['name' => 'deponent_id',      'label' => 'National ID Number',   'type' => 'text',    'required' => true,  'placeholder' => 'e.g. 45678901'],
                    ['name' => 'deponent_address', 'label' => 'Residential Address',  'type' => 'textarea','required' => true,  'placeholder' => 'Village/Estate, Location, County'],
                    ['name' => 'deponent_phone',   'label' => 'Phone Number',         'type' => 'phone',   'required' => true,  'placeholder' => '07XXXXXXXX'],
                    ['name' => 'affidavit_subject','label' => 'Subject / Matter',     'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Confirmation of Ownership of Motor Vehicle'],
                    ['name' => 'declaration_body', 'label' => 'Declarations (numbered)', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Write each declaration starting with THAT...', 'help' => 'Enter each declaration point. They will be numbered automatically.'],
                    ['name' => 'date',             'label' => 'Date of Swearing',     'type' => 'date',    'required' => true],
                ],
                'definition' => [
                    'title' => 'General Affidavit',
                    'sections' => [
                        [
                            'heading'       => 'REPUBLIC OF KENYA',
                            'body_template' => "IN THE MATTER OF THE OATHS AND STATUTORY DECLARATIONS ACT (Cap. 15)\n\nAFFIDAVIT\n\nRE: {{affidavit_subject}}",
                        ],
                        [
                            'heading'       => null,
                            'body_template' => "I, {{deponent_name}}, holder of National ID No. {{deponent_id}}, of {{deponent_address}}, phone number {{deponent_phone}}, do hereby make oath and solemnly declare as follows:",
                        ],
                        [
                            'heading'       => null,
                            'body_template' => "{{declaration_body}}\n\nTHAT the contents of this affidavit are true to the best of my knowledge and belief.",
                        ],
                        [
                            'heading'       => null,
                            'body_template' => "SWORN at __________________ this {{date}}\n\nDEPONENT: _______________________________\n{{deponent_name}}\nID No: {{deponent_id}}\n\nBEFORE ME:\n\n_______________________________\nCOMMISSIONER FOR OATHS / MAGISTRATE\nStamp & Signature",
                        ],
                    ],
                ],
            ],

            // ─── 10. Employment Letter ────────────────────────────────────────────
            [
                'category_slug' => 'employment',
                'name'          => 'Employment Letter',
                'slug'          => 'employment-letter',
                'description'   => 'An official employment confirmation letter from an employer.',
                'price'         => '100.00',
                'sort_order'    => 10,
                'schema'        => [
                    ['name' => 'employer_name',      'label' => 'Employer / Company Name',    'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Safaricom PLC'],
                    ['name' => 'employer_address',   'label' => 'Company Address',             'type' => 'textarea','required' => true,  'placeholder' => 'P.O. Box, Location'],
                    ['name' => 'employer_phone',     'label' => 'Company Phone',               'type' => 'phone',   'required' => true,  'placeholder' => '07XXXXXXXX'],
                    ['name' => 'employer_email',     'label' => 'Company Email',               'type' => 'email',   'required' => false, 'placeholder' => 'hr@company.co.ke'],
                    ['name' => 'hr_signatory',       'label' => 'Signatory Name & Title',      'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Mr. Tom Njoroge, HR Manager'],
                    ['name' => 'date',               'label' => 'Date',                        'type' => 'date',    'required' => true],
                    ['name' => 'employee_name',      'label' => 'Employee Full Name',          'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Grace Wanjiku Njoro'],
                    ['name' => 'employee_id_no',     'label' => 'Employee ID / Staff Number',  'type' => 'text',    'required' => false, 'placeholder' => 'e.g. EMP-2045'],
                    ['name' => 'job_title',          'label' => 'Job Title',                   'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Senior Software Developer'],
                    ['name' => 'department',         'label' => 'Department',                  'type' => 'text',    'required' => false, 'placeholder' => 'e.g. Information Technology'],
                    ['name' => 'employment_type',    'label' => 'Employment Type',             'type' => 'select',  'required' => true,  'options' => ['Permanent', 'Contract', 'Part-time', 'Intern']],
                    ['name' => 'start_date',         'label' => 'Employment Start Date',       'type' => 'date',    'required' => true],
                    ['name' => 'gross_salary',       'label' => 'Gross Monthly Salary (KSh)',  'type' => 'number',  'required' => true,  'placeholder' => 'e.g. 80000', 'help' => 'Enter the gross monthly salary in Kenya Shillings.'],
                    ['name' => 'purpose',            'label' => 'Purpose of Letter',           'type' => 'text',    'required' => true,  'placeholder' => 'e.g. bank loan application, visa application'],
                ],
                'definition' => [
                    'title' => 'Employment Confirmation Letter',
                    'sections' => [
                        [
                            'heading'       => null,
                            'body_template' => "{{employer_name}}\n{{employer_address}}\nTel: {{employer_phone}}\nEmail: {{employer_email}}\n\n{{date}}",
                        ],
                        [
                            'heading'       => 'TO WHOM IT MAY CONCERN',
                            'body_template' => "RE: EMPLOYMENT CONFIRMATION — {{employee_name}}\n\nThis is to confirm that {{employee_name}} (Staff No: {{employee_id_no}}) is a {{employment_type}} employee of {{employer_name}}.\n\nPosition: {{job_title}}\nDepartment: {{department}}\nDate of Employment: {{start_date}}\nGross Monthly Salary: KSh {{gross_salary}}\n\nThis letter has been issued at the request of the employee for the purpose of {{purpose}}.\n\nYours faithfully,\n\n\n{{hr_signatory}}\n{{employer_name}}",
                        ],
                    ],
                ],
            ],

            // ─── 11. Business Introduction Letter ────────────────────────────────
            [
                'category_slug' => 'business',
                'name'          => 'Business Introduction Letter',
                'slug'          => 'business-introduction',
                'description'   => 'A letter introducing a company or business to potential clients or partners.',
                'price'         => '100.00',
                'sort_order'    => 11,
                'schema'        => [
                    ['name' => 'company_name',      'label' => 'Your Company Name',         'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Sunrise Enterprises Ltd'],
                    ['name' => 'company_address',   'label' => 'Company Address',            'type' => 'textarea','required' => true,  'placeholder' => 'P.O. Box, Location'],
                    ['name' => 'company_phone',     'label' => 'Company Phone',              'type' => 'phone',   'required' => true,  'placeholder' => '07XXXXXXXX'],
                    ['name' => 'company_email',     'label' => 'Company Email',              'type' => 'email',   'required' => false, 'placeholder' => 'info@yourcompany.co.ke'],
                    ['name' => 'company_reg_no',    'label' => 'Registration Number',        'type' => 'text',    'required' => false, 'placeholder' => 'e.g. CPR/2024/012345'],
                    ['name' => 'signatory_name',    'label' => 'Signatory Name & Title',     'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Mr. Kevin Otieno, Director'],
                    ['name' => 'date',              'label' => 'Date',                       'type' => 'date',    'required' => true],
                    ['name' => 'recipient_name',    'label' => 'Recipient Name / Title',     'type' => 'text',    'required' => true,  'placeholder' => 'e.g. The Procurement Manager'],
                    ['name' => 'recipient_company', 'label' => 'Recipient Organisation',     'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Kenya Power & Lighting Co.'],
                    ['name' => 'business_nature',   'label' => 'Nature of Business',         'type' => 'textarea','required' => true,  'placeholder' => 'Describe the products/services your company offers'],
                    ['name' => 'value_proposition', 'label' => 'Why Choose You?',            'type' => 'textarea','required' => true,  'placeholder' => 'Key reasons why the recipient should work with you'],
                ],
                'definition' => [
                    'title' => 'Business Introduction Letter',
                    'sections' => [
                        [
                            'heading'       => null,
                            'body_template' => "{{company_name}}\n{{company_address}}\nTel: {{company_phone}}\nEmail: {{company_email}}\nReg No: {{company_reg_no}}\n\n{{date}}",
                        ],
                        [
                            'heading'       => null,
                            'body_template' => "{{recipient_name}}\n{{recipient_company}}",
                        ],
                        [
                            'heading'       => 'Dear {{recipient_name}},',
                            'body_template' => "RE: INTRODUCTION OF {{company_name}}\n\nWe are pleased to introduce ourselves as {{company_name}}, a reputable company offering the following services/products:\n\n{{business_nature}}\n\nWhy partner with us:\n\n{{value_proposition}}\n\nWe believe that our services align with your needs and we would be honoured to work with {{recipient_company}}. We would welcome the opportunity to present our full capability profile at your convenience.\n\nPlease do not hesitate to contact us for further information.\n\nYours faithfully,\n\n\n{{signatory_name}}\n{{company_name}}\nTel: {{company_phone}}",
                        ],
                    ],
                ],
            ],

            // ─── 12. CBO Introduction Letter ─────────────────────────────────────
            [
                'category_slug' => 'community',
                'name'          => 'CBO Introduction Letter',
                'slug'          => 'cbo-introduction',
                'description'   => 'A Community Based Organisation (CBO) introduction letter to stakeholders or government offices.',
                'price'         => '100.00',
                'sort_order'    => 12,
                'schema'        => [
                    ['name' => 'cbo_name',         'label' => 'CBO Name',                   'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Umoja Community Development Group'],
                    ['name' => 'cbo_reg_no',       'label' => 'Registration Number',         'type' => 'text',    'required' => false, 'placeholder' => 'e.g. NGO/CBO/2023/04567'],
                    ['name' => 'cbo_location',     'label' => 'CBO Location',                'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Kangemi, Westlands, Nairobi'],
                    ['name' => 'cbo_phone',        'label' => 'CBO Phone',                   'type' => 'phone',   'required' => true,  'placeholder' => '07XXXXXXXX'],
                    ['name' => 'chairperson_name', 'label' => 'Chairperson Full Name',        'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Mrs. Esther Chepkoech'],
                    ['name' => 'secretary_name',   'label' => 'Secretary Full Name',          'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Mr. Paul Kipchoge'],
                    ['name' => 'date',             'label' => 'Date',                        'type' => 'date',    'required' => true],
                    ['name' => 'recipient_name',   'label' => 'Recipient Name / Office',      'type' => 'text',    'required' => true,  'placeholder' => 'e.g. The Sub-County Director of Social Services'],
                    ['name' => 'cbo_objectives',   'label' => 'CBO Objectives / Activities', 'type' => 'textarea','required' => true,  'placeholder' => 'Describe the main objectives and activities of the CBO'],
                    ['name' => 'membership_count', 'label' => 'Number of Members',            'type' => 'number',  'required' => true,  'placeholder' => 'e.g. 45'],
                    ['name' => 'support_sought',   'label' => 'Support / Purpose of Letter', 'type' => 'textarea','required' => true,  'placeholder' => 'What support or recognition are you seeking?'],
                ],
                'definition' => [
                    'title' => 'CBO Introduction Letter',
                    'sections' => [
                        [
                            'heading'       => null,
                            'body_template' => "{{cbo_name}}\nReg No: {{cbo_reg_no}}\n{{cbo_location}}\nTel: {{cbo_phone}}\n\n{{date}}",
                        ],
                        [
                            'heading'       => null,
                            'body_template' => "{{recipient_name}}",
                        ],
                        [
                            'heading'       => 'Dear Sir/Madam,',
                            'body_template' => "RE: INTRODUCTION OF {{cbo_name}}\n\nWe write to introduce {{cbo_name}}, a Community Based Organisation registered and operating in {{cbo_location}} with Registration Number {{cbo_reg_no}}.\n\nOur Objectives and Activities:\n{{cbo_objectives}}\n\nOur organisation has a membership of {{membership_count}} active members who are committed to community development.\n\n{{support_sought}}\n\nWe look forward to a positive response and fruitful cooperation.\n\nYours faithfully,\n\n\n{{chairperson_name}}\nChairperson\n{{cbo_name}}\n\n{{secretary_name}}\nSecretary\n{{cbo_name}}\nTel: {{cbo_phone}}",
                        ],
                    ],
                ],
            ],

            // ─── 13. Meeting Minutes ──────────────────────────────────────────────
            [
                'category_slug' => 'business',
                'name'          => 'Meeting Minutes',
                'slug'          => 'meeting-minutes',
                'description'   => 'Official minutes for a business or community meeting.',
                'price'         => '100.00',
                'sort_order'    => 13,
                'schema'        => [
                    ['name' => 'organisation_name', 'label' => 'Organisation / Company Name', 'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Pamoja Savings Group'],
                    ['name' => 'meeting_type',      'label' => 'Type of Meeting',             'type' => 'select',  'required' => true,  'options' => ['Annual General Meeting', 'Special General Meeting', 'Board Meeting', 'Committee Meeting', 'Regular Meeting']],
                    ['name' => 'meeting_date',      'label' => 'Date of Meeting',             'type' => 'date',    'required' => true],
                    ['name' => 'meeting_time',      'label' => 'Time of Meeting',             'type' => 'text',    'required' => true,  'placeholder' => 'e.g. 10:00 AM'],
                    ['name' => 'meeting_venue',     'label' => 'Venue',                       'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Community Hall, Kibera'],
                    ['name' => 'chairperson',       'label' => 'Chairperson',                 'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Mr. John Mwangi'],
                    ['name' => 'secretary',         'label' => 'Secretary',                   'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Ms. Fatuma Hassan'],
                    ['name' => 'members_present',   'label' => 'Members Present',             'type' => 'textarea','required' => true,  'placeholder' => 'List names and roles, one per line'],
                    ['name' => 'apologies',         'label' => 'Apologies / Absent',          'type' => 'textarea','required' => false, 'placeholder' => 'List names of members who sent apologies'],
                    ['name' => 'agenda_items',      'label' => 'Agenda Items',                'type' => 'textarea','required' => true,  'placeholder' => 'List agenda items, one per line', 'help' => 'Enter each agenda item on a new line.'],
                    ['name' => 'discussions',       'label' => 'Discussions & Resolutions',   'type' => 'textarea','required' => true,  'placeholder' => 'Summarise discussions and any resolutions passed'],
                    ['name' => 'next_meeting_date', 'label' => 'Next Meeting Date',            'type' => 'date',    'required' => false],
                ],
                'definition' => [
                    'title' => 'Meeting Minutes',
                    'sections' => [
                        [
                            'heading'       => '{{organisation_name}}',
                            'body_template' => "MINUTES OF THE {{meeting_type}}\nHeld on {{meeting_date}} at {{meeting_time}}\nVenue: {{meeting_venue}}",
                        ],
                        [
                            'heading'       => '1. ATTENDANCE',
                            'body_template' => "Chairperson: {{chairperson}}\nSecretary: {{secretary}}\n\nMembers Present:\n{{members_present}}\n\nApologies:\n{{apologies}}",
                        ],
                        [
                            'heading'       => '2. AGENDA',
                            'body_template' => "{{agenda_items}}",
                        ],
                        [
                            'heading'       => '3. DISCUSSIONS AND RESOLUTIONS',
                            'body_template' => "{{discussions}}",
                        ],
                        [
                            'heading'       => '4. NEXT MEETING',
                            'body_template' => "The next meeting is scheduled for {{next_meeting_date}}.\n\n_______________________________\n{{chairperson}}\nChairperson\n\n_______________________________\n{{secretary}}\nSecretary",
                        ],
                    ],
                ],
            ],

            // ─── 14. Rental Agreement ─────────────────────────────────────────────
            [
                'category_slug' => 'agreements',
                'name'          => 'Rental Agreement',
                'slug'          => 'rental-agreement',
                'description'   => 'A simple residential or commercial rental/tenancy agreement.',
                'price'         => '150.00',
                'sort_order'    => 14,
                'schema'        => [
                    ['name' => 'landlord_name',     'label' => 'Landlord Full Name',          'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Peter Kamau Wanjiku'],
                    ['name' => 'landlord_id',       'label' => 'Landlord National ID',         'type' => 'text',    'required' => true,  'placeholder' => 'e.g. 12345678'],
                    ['name' => 'landlord_phone',    'label' => 'Landlord Phone',               'type' => 'phone',   'required' => true,  'placeholder' => '07XXXXXXXX'],
                    ['name' => 'tenant_name',       'label' => 'Tenant Full Name',             'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Mary Njeri Kamau'],
                    ['name' => 'tenant_id',         'label' => 'Tenant National ID',           'type' => 'text',    'required' => true,  'placeholder' => 'e.g. 23456789'],
                    ['name' => 'tenant_phone',      'label' => 'Tenant Phone',                 'type' => 'phone',   'required' => true,  'placeholder' => '07XXXXXXXX'],
                    ['name' => 'property_address',  'label' => 'Property Address',             'type' => 'textarea','required' => true,  'placeholder' => 'Full address of the rented property'],
                    ['name' => 'property_type',     'label' => 'Property Type',                'type' => 'select',  'required' => true,  'options' => ['Bedsitter', 'Single Room', '1-Bedroom', '2-Bedroom', '3-Bedroom', 'Shop / Commercial', 'Warehouse', 'Other']],
                    ['name' => 'monthly_rent',      'label' => 'Monthly Rent (KSh)',           'type' => 'number',  'required' => true,  'placeholder' => 'e.g. 12000'],
                    ['name' => 'deposit_amount',    'label' => 'Security Deposit (KSh)',       'type' => 'number',  'required' => true,  'placeholder' => 'e.g. 24000'],
                    ['name' => 'commencement_date', 'label' => 'Commencement Date',            'type' => 'date',    'required' => true],
                    ['name' => 'tenancy_duration',  'label' => 'Tenancy Duration',             'type' => 'text',    'required' => true,  'placeholder' => 'e.g. 12 months / Month-to-month'],
                    ['name' => 'notice_period',     'label' => 'Notice Period',                'type' => 'text',    'required' => true,  'placeholder' => 'e.g. 1 month', 'help' => 'Notice period required to terminate tenancy.'],
                    ['name' => 'additional_terms',  'label' => 'Additional Terms (optional)',   'type' => 'textarea','required' => false, 'placeholder' => 'Any additional conditions agreed upon'],
                    ['name' => 'agreement_date',    'label' => 'Agreement Date',               'type' => 'date',    'required' => true],
                ],
                'definition' => [
                    'title' => 'Residential / Commercial Rental Agreement',
                    'sections' => [
                        [
                            'heading'       => 'RENTAL AGREEMENT',
                            'body_template' => "This Rental Agreement is entered into on {{agreement_date}} between:\n\nLANDLORD:\nName: {{landlord_name}}\nNational ID: {{landlord_id}}\nPhone: {{landlord_phone}}\n\nAnd\n\nTENANT:\nName: {{tenant_name}}\nNational ID: {{tenant_id}}\nPhone: {{tenant_phone}}",
                        ],
                        [
                            'heading'       => '1. PREMISES',
                            'body_template' => "The Landlord hereby lets and the Tenant hereby takes the premises described as follows:\n{{property_address}} (the \"Premises\"), being a {{property_type}}.",
                        ],
                        [
                            'heading'       => '2. TERM',
                            'body_template' => "The tenancy shall commence on {{commencement_date}} and shall continue for {{tenancy_duration}}, unless sooner determined in accordance with this Agreement.",
                        ],
                        [
                            'heading'       => '3. RENT',
                            'body_template' => "The Tenant shall pay a monthly rent of KSh {{monthly_rent}} (Kenya Shillings {{monthly_rent}} only), payable in advance on or before the 5th day of each month.",
                        ],
                        [
                            'heading'       => '4. SECURITY DEPOSIT',
                            'body_template' => "The Tenant shall pay a refundable security deposit of KSh {{deposit_amount}} upon signing this Agreement. The deposit shall be refunded within 30 days of vacating the premises, subject to any deductions for damages or unpaid rent.",
                        ],
                        [
                            'heading'       => '5. TERMINATION',
                            'body_template' => "Either party may terminate this Agreement by giving {{notice_period}} written notice to the other party.",
                        ],
                        [
                            'heading'       => '6. ADDITIONAL TERMS',
                            'body_template' => "{{additional_terms}}",
                        ],
                        [
                            'heading'       => '7. SIGNATURES',
                            'body_template' => "IN WITNESS WHEREOF the parties have signed this Agreement on the date first above written.\n\nLANDLORD:\n_______________________________\n{{landlord_name}}\nID No: {{landlord_id}}\nDate: {{agreement_date}}\n\nTENANT:\n_______________________________\n{{tenant_name}}\nID No: {{tenant_id}}\nDate: {{agreement_date}}\n\nWITNESS:\n_______________________________\nName:\nID No:\nDate:",
                        ],
                    ],
                ],
            ],

            // ─── 15. Sale Agreement ───────────────────────────────────────────────
            [
                'category_slug' => 'agreements',
                'name'          => 'Sale Agreement',
                'slug'          => 'sale-agreement',
                'description'   => 'A sale agreement for goods, motor vehicles, or other personal property.',
                'price'         => '150.00',
                'sort_order'    => 15,
                'schema'        => [
                    ['name' => 'seller_name',        'label' => 'Seller Full Name',            'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Joseph Kariuki Mwangi'],
                    ['name' => 'seller_id',          'label' => 'Seller National ID',           'type' => 'text',    'required' => true,  'placeholder' => 'e.g. 12345678'],
                    ['name' => 'seller_phone',       'label' => 'Seller Phone',                 'type' => 'phone',   'required' => true,  'placeholder' => '07XXXXXXXX'],
                    ['name' => 'buyer_name',         'label' => 'Buyer Full Name',              'type' => 'text',    'required' => true,  'placeholder' => 'e.g. Grace Achieng Omondi'],
                    ['name' => 'buyer_id',           'label' => 'Buyer National ID',            'type' => 'text',    'required' => true,  'placeholder' => 'e.g. 23456789'],
                    ['name' => 'buyer_phone',        'label' => 'Buyer Phone',                  'type' => 'phone',   'required' => true,  'placeholder' => '07XXXXXXXX'],
                    ['name' => 'item_description',   'label' => 'Item Description',             'type' => 'textarea','required' => true,  'placeholder' => 'Describe the item being sold (make, model, condition, serial number if applicable)'],
                    ['name' => 'sale_price',         'label' => 'Agreed Sale Price (KSh)',      'type' => 'number',  'required' => true,  'placeholder' => 'e.g. 450000'],
                    ['name' => 'payment_method',     'label' => 'Payment Method',               'type' => 'select',  'required' => true,  'options' => ['Cash', 'M-Pesa', 'Bank Transfer', 'Cheque', 'Instalment']],
                    ['name' => 'deposit_paid',       'label' => 'Deposit Already Paid (KSh)',   'type' => 'number',  'required' => false, 'placeholder' => 'e.g. 50000 (0 if none)'],
                    ['name' => 'balance_due_date',   'label' => 'Balance Due Date',             'type' => 'date',    'required' => false, 'help' => 'Date by which the full balance must be paid.'],
                    ['name' => 'handover_date',      'label' => 'Handover Date',                'type' => 'date',    'required' => true],
                    ['name' => 'warranties',         'label' => 'Warranties / Conditions',      'type' => 'textarea','required' => false, 'placeholder' => 'Any warranties given or conditions of sale (e.g. sold as seen)'],
                    ['name' => 'agreement_date',     'label' => 'Agreement Date',               'type' => 'date',    'required' => true],
                ],
                'definition' => [
                    'title' => 'Sale Agreement',
                    'sections' => [
                        [
                            'heading'       => 'SALE AGREEMENT',
                            'body_template' => "This Sale Agreement is entered into on {{agreement_date}} between:\n\nSELLER:\nName: {{seller_name}}\nNational ID: {{seller_id}}\nPhone: {{seller_phone}}\n\nAnd\n\nBUYER:\nName: {{buyer_name}}\nNational ID: {{buyer_id}}\nPhone: {{buyer_phone}}",
                        ],
                        [
                            'heading'       => '1. ITEM OF SALE',
                            'body_template' => "The Seller agrees to sell and the Buyer agrees to purchase the following:\n\n{{item_description}}",
                        ],
                        [
                            'heading'       => '2. PURCHASE PRICE',
                            'body_template' => "The agreed purchase price is KSh {{sale_price}} (Kenya Shillings {{sale_price}} only).\n\nDeposit paid: KSh {{deposit_paid}}\nBalance due: KSh {{sale_price}} less {{deposit_paid}}\nBalance payment method: {{payment_method}}\nBalance due date: {{balance_due_date}}",
                        ],
                        [
                            'heading'       => '3. HANDOVER',
                            'body_template' => "The Seller shall hand over the item to the Buyer on {{handover_date}}, upon confirmation of full payment.",
                        ],
                        [
                            'heading'       => '4. WARRANTIES AND CONDITIONS',
                            'body_template' => "{{warranties}}",
                        ],
                        [
                            'heading'       => '5. GENERAL',
                            'body_template' => "5.1 Title to the item shall pass to the Buyer only upon receipt of full payment.\n5.2 This Agreement constitutes the entire agreement between the parties and supersedes all prior negotiations.\n5.3 Any amendments to this Agreement must be in writing and signed by both parties.",
                        ],
                        [
                            'heading'       => '6. SIGNATURES',
                            'body_template' => "IN WITNESS WHEREOF the parties have signed this Agreement on the date stated above.\n\nSELLER:\n_______________________________\n{{seller_name}}\nID No: {{seller_id}}\nDate: {{agreement_date}}\n\nBUYER:\n_______________________________\n{{buyer_name}}\nID No: {{buyer_id}}\nDate: {{agreement_date}}\n\nWITNESS 1:\n_______________________________\nName:\nID No:\nDate:\n\nWITNESS 2:\n_______________________________\nName:\nID No:\nDate:",
                        ],
                    ],
                ],
            ],

        ]; // end $templates

        foreach ($templates as $data) {
            $categoryId = $categories[$data['category_slug']] ?? null;

            if (! $categoryId) {
                $this->command->warn("Category slug '{$data['category_slug']}' not found — skipping {$data['slug']}");
                continue;
            }

            Template::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id'      => $categoryId,
                    'name'             => $data['name'],
                    'slug'             => $data['slug'],
                    'description'      => $data['description'],
                    'price'            => $data['price'],
                    'schema'           => $data['schema'],
                    'definition'       => $data['definition'],
                    'is_active'        => false,
                    'sort_order'       => $data['sort_order'],
                    'meta_title'       => null,
                    'meta_description' => null,
                ]
            );
        }
    }
}
