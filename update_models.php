<?php

$models = [
    'c:\xampp\htdocs\foms_bms\app\Models\Student.php',
    'c:\xampp\htdocs\foms_bms\app\Models\Teacher.php',
    'c:\xampp\htdocs\foms_bms\app\Models\StudentLead.php',
    'c:\xampp\htdocs\foms_bms\app\Models\TeacherLead.php',
];

foreach ($models as $path) {
    if (!file_exists($path)) continue;
    
    $content = file_get_contents($path);
    
    // Replace getFormattedContactNumberAttribute
    $oldContact = <<<EOD
    public function getFormattedContactNumberAttribute()
    {
        \$code = \$this->country?->code ?? '';
        return \$code ? \$code . ' ' . \$this->contact_number : \$this->contact_number;
    }
EOD;

    $newContact = <<<EOD
    public function getFormattedContactNumberAttribute()
    {
        \$code = \$this->country?->code ?? '';
        \$name = \$this->country?->name ?? '';
        \$prefix = \$code && \$name ? "\$code (\$name) " : (\$code ? "\$code " : '');
        return \$prefix . \$this->contact_number;
    }
EOD;

    $content = str_replace($oldContact, $newContact, $content);
    
    // Replace getFormattedPhoneAttribute
    $oldPhone = <<<EOD
    public function getFormattedPhoneAttribute()
    {
        \$code = \$this->country?->code ?? '';
        return \$code ? \$code . ' ' . \$this->phone : \$this->phone;
    }
EOD;

    $newPhone = <<<EOD
    public function getFormattedPhoneAttribute()
    {
        \$code = \$this->country?->code ?? '';
        \$name = \$this->country?->name ?? '';
        \$prefix = \$code && \$name ? "\$code (\$name) " : (\$code ? "\$code " : '');
        return \$prefix . \$this->phone;
    }
EOD;

    $content = str_replace($oldPhone, $newPhone, $content);
    
    file_put_contents($path, $content);
    echo "Updated $path\n";
}
