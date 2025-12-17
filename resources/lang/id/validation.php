<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'Bidang :attribute harus diterima.',
    'active_url'           => 'Bidang :attribute bukan URL yang valid.',
    'after'                => 'Bidang :attribute harus tanggal setelah :date.',
    'after_or_equal'       => 'Bidang :attribute harus tanggal setelah atau sama dengan :date.',
    'alpha'                => 'Bidang :attribute hanya boleh berisi huruf.',
    'alpha_dash'           => 'Bidang :attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num'            => 'Bidang :attribute hanya boleh berisi huruf dan angka.',
    'array'                => 'Bidang :attribute harus berupa larik.',
    'before'               => 'Bidang :attribute harus tanggal sebelum :date.',
    'before_or_equal'      => 'Bidang :attribute harus tanggal sebelum atau sama dengan :date.',
    'between'              => [
        'numeric' => 'Bidang :attribute harus antara :min dan :max.',
        'file'    => 'Bidang :attribute harus antara :min dan :max kilobyte.',
        'string'  => 'Bidang :attribute harus antara :min dan :max karakter.',
        'array'   => 'Bidang :attribute harus memiliki antara :min dan :max item.',
    ],
    'boolean'              => 'Bidang :attribute harus berupa true atau false.',
    'confirmed'            => 'Konfirmasi :attribute tidak cocok.',
    'date'                 => 'Bidang :attribute bukan tanggal yang valid.',
    'date_equals'          => 'Bidang :attribute harus tanggal yang sama dengan :date.',
    'date_format'          => 'Bidang :attribute tidak cocok dengan format :format.',
    'different'            => 'Bidang :attribute dan :other harus berbeda.',
    'digits'               => 'Bidang :attribute harus :digits digit.',
    'digits_between'       => 'Bidang :attribute harus antara :min dan :max digit.',
    'dimensions'           => 'Bidang :attribute memiliki dimensi gambar yang tidak valid.',
    'distinct'             => 'Bidang :attribute memiliki nilai duplikat.',
    'email'                => 'Bidang :attribute harus berupa alamat email yang valid.',
    'exists'               => 'Bidang :attribute yang dipilih tidak valid.',
    'file'                 => 'Bidang :attribute harus berupa file.',
    'filled'               => 'Bidang :attribute harus memiliki nilai.',
    'gt'                   => [
        'numeric' => 'Bidang :attribute harus lebih besar dari :value.',
        'file'    => 'Bidang :attribute harus lebih besar dari :value kilobyte.',
        'string'  => 'Bidang :attribute harus lebih besar dari :value karakter.',
        'array'   => 'Bidang :attribute harus memiliki lebih dari :value item.',
    ],
    'gte'                  => [
        'numeric' => 'Bidang :attribute harus lebih besar dari atau sama dengan :value.',
        'file'    => 'Bidang :attribute harus lebih besar dari atau sama dengan :value kilobyte.',
        'string'  => 'Bidang :attribute harus lebih besar dari atau sama dengan :value karakter.',
        'array'   => 'Bidang :attribute harus memiliki :value item atau lebih.',
    ],
    'image'                => 'Bidang :attribute harus berupa gambar.',
    'in'                   => 'Bidang :attribute yang dipilih tidak valid.',
    'in_array'             => 'Bidang :attribute tidak ada di :other.',
    'integer'              => 'Bidang :attribute harus berupa bilangan bulat.',
    'ip'                   => 'Bidang :attribute harus berupa alamat IP yang valid.',
    'ipv4'                 => 'Bidang :attribute harus berupa alamat IPv4 yang valid.',
    'ipv6'                 => 'Bidang :attribute harus berupa alamat IPv6 yang valid.',
    'json'                 => 'Bidang :attribute harus berupa string JSON yang valid.',
    'lt'                   => [
        'numeric' => 'Bidang :attribute harus kurang dari :value.',
        'file'    => 'Bidang :attribute harus kurang dari :value kilobyte.',
        'string'  => 'Bidang :attribute harus kurang dari :value karakter.',
        'array'   => 'Bidang :attribute harus memiliki kurang dari :value item.',
    ],
    'lte'                  => [
        'numeric' => 'Bidang :attribute harus kurang dari atau sama dengan :value.',
        'file'    => 'Bidang :attribute harus kurang dari atau sama dengan :value kilobyte.',
        'string'  => 'Bidang :attribute harus kurang dari atau sama dengan :value karakter.',
        'array'   => 'Bidang :attribute tidak boleh memiliki lebih dari :value item.',
    ],
    'max'                  => [
        'numeric' => 'Bidang :attribute tidak boleh lebih dari :max.',
        'file'    => 'Bidang :attribute tidak boleh lebih dari :max kilobyte.',
        'string'  => 'Bidang :attribute tidak boleh lebih dari :max karakter.',
        'array'   => 'Bidang :attribute tidak boleh memiliki lebih dari :max item.',
    ],
    'mimes'                => 'Bidang :attribute harus berupa file bertipe: :values.',
    'mimetypes'            => 'Bidang :attribute harus berupa file bertipe: :values.',
    'min'                  => [
        'numeric' => 'Bidang :attribute minimal harus :min.',
        'file'    => 'Bidang :attribute minimal harus :min kilobyte.',
        'string'  => 'Bidang :attribute minimal harus :min karakter.',
        'array'   => 'Bidang :attribute minimal harus memiliki :min item.',
    ],
    'not_in'               => 'Bidang :attribute yang dipilih tidak valid.',
    'not_regex'            => 'Format bidang :attribute tidak valid.',
    'numeric'              => 'Bidang :attribute harus berupa angka.',
    'present'              => 'Bidang :attribute harus ada.',
    'regex'                => 'Format bidang :attribute tidak valid.',
    'required'             => 'Bidang :attribute wajib diisi.',
    'required_if'          => 'Bidang :attribute wajib diisi ketika :other adalah :value.',
    'required_unless'      => 'Bidang :attribute wajib diisi kecuali :other ada di :values.',
    'required_with'        => 'Bidang :attribute wajib diisi ketika :values ada.',
    'required_with_all'    => 'Bidang :attribute wajib diisi ketika :values ada.',
    'required_without'     => 'Bidang :attribute wajib diisi ketika :values tidak ada.',
    'required_without_all' => 'Bidang :attribute wajib diisi ketika tidak ada satupun dari :values yang ada.',
    'same'                 => 'Bidang :attribute dan :other harus cocok.',
    'size'                 => [
        'numeric' => 'Bidang :attribute harus berukuran :size.',
        'file'    => 'Bidang :attribute harus berukuran :size kilobyte.',
        'string'  => 'Bidang :attribute harus berukuran :size karakter.',
        'array'   => 'Bidang :attribute harus mengandung :size item.',
    ],
    'starts_with'          => 'Bidang :attribute harus diawali dengan salah satu dari berikut: :values.',
    'string'               => 'Bidang :attribute harus berupa string.',
    'timezone'             => 'Bidang :attribute harus berupa zona waktu yang valid.',
    'unique'               => 'Bidang :attribute sudah digunakan.',
    'uploaded'             => 'Bidang :attribute gagal diunggah.',
    'url'                  => 'Format bidang :attribute tidak valid.',
    'uuid'                 => 'Bidang :attribute harus berupa UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to make the messages more expressive.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more readable such as "E-Mail Address" instead of "email".
    | This simply helps us make messages more expressive.
    |
    */

    'attributes' => [
        'email' => 'Alamat Email',
        'password' => 'Kata Sandi',
        'code' => 'Kode Verifikasi',
        // Tambahkan atribut lain yang Anda gunakan di form
    ],

];