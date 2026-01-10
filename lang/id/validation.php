<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validasi Bahasa
    |--------------------------------------------------------------------------
    |
    | Baris bahasa berikut berisi pesan kesalahan standar yang digunakan oleh
    | kelas validasi. Beberapa aturan ini memiliki beberapa versi seperti
    | aturan ukuran. Jangan ragu untuk mengasah setiap pesan di sini.
    |
    */

    'accepted'        => 'Bidang :attribute harus diterima.',
    'active_url'      => 'Bidang :attribute bukan URL yang valid.',
    'after'           => 'Bidang :attribute harus berupa tanggal setelah :date.',
    'after_or_equal'  => 'Bidang :attribute harus berupa tanggal setelah atau sama dengan :date.',
    'alpha'           => 'Bidang :attribute hanya boleh berisi huruf.',
    'alpha_dash'      => 'Bidang :attribute hanya boleh berisi huruf, angka, setrip, dan garis bawah.',
    'alpha_num'       => 'Bidang :attribute hanya boleh berisi huruf dan angka.',
    'array'           => 'Bidang :attribute harus berupa array.',
    'before'          => 'Bidang :attribute harus berupa tanggal sebelum :date.',
    'before_or_equal' => 'Bidang :attribute harus berupa tanggal sebelum atau sama dengan :date.',
    'between'         => [
        'numeric' => 'Bidang :attribute harus antara :min dan :max.',
        'file'    => 'Bidang :attribute harus antara :min dan :max kilobyte.',
        'string'  => 'Bidang :attribute harus antara :min dan :max karakter.',
        'array'   => 'Bidang :attribute harus antara :min dan :max item.',
    ],
    'boolean'        => 'Bidang :attribute harus berupa true atau false.',
    'confirmed'      => 'Konfirmasi :attribute tidak cocok.',
    'date'           => 'Bidang :attribute bukan tanggal yang valid.',
    'date_equals'    => 'Bidang :attribute harus berupa tanggal yang sama dengan :date.',
    'date_format'    => 'Bidang :attribute tidak cocok dengan format :format.',
    'declined'       => 'Bidang :attribute harus ditolak.',
    'declined_if'    => 'Bidang :attribute harus ditolak jika :other adalah :value.',
    'different'      => 'Bidang :attribute dan :other harus berbeda.',
    'digits'         => 'Bidang :attribute harus berupa angka :digits.',
    'digits_between' => 'Bidang :attribute harus antara angka :min dan :max.',
    'dimensions'     => 'Bidang :attribute memiliki dimensi gambar yang tidak valid.',
    'distinct'       => 'Bidang :attribute memiliki nilai yang duplikat.',
    'doesnt_start_with' => 'Bidang :attribute tidak boleh dimulai dengan salah satu dari berikut ini: :values.',
    'email'          => 'Bidang :attribute harus berupa alamat email yang valid.',
    'ends_with'      => 'Bidang :attribute harus diakhiri dengan salah satu dari berikut ini: :values.',
    'enum'           => ':attribute yang dipilih tidak valid.',
    'exists'         => ':attribute yang dipilih tidak valid.',
    'file'           => 'Bidang :attribute harus berupa sebuah berkas.',
    'filled'         => 'Bidang :attribute harus memiliki nilai.',
    'gt'             => [
        'numeric' => 'Bidang :attribute harus lebih besar dari :value.',
        'file'    => 'Bidang :attribute harus lebih besar dari :value kilobyte.',
        'string'  => 'Bidang :attribute harus lebih besar dari :value karakter.',
        'array'   => 'Bidang :attribute harus memiliki lebih dari :value item.',
    ],
    'gte' => [
        'numeric' => 'Bidang :attribute harus lebih besar dari atau sama dengan :value.',
        'file'    => 'Bidang :attribute harus lebih besar dari atau sama dengan :value kilobyte.',
        'string'  => 'Bidang :attribute harus lebih besar dari atau sama dengan :value karakter.',
        'array'   => 'Bidang :attribute harus memiliki :value item atau lebih.',
    ],
    'image'    => 'Bidang :attribute harus berupa gambar.',
    'in'       => 'Bidang :attribute yang dipilih tidak valid.',
    'in_array' => 'Bidang :attribute tidak ada di :other.',
    'integer'  => 'Bidang :attribute harus berupa bilangan bulat.',
    'ip'       => 'Bidang :attribute harus berupa alamat IP yang valid.',
    'ipv4'     => 'Bidang :attribute harus berupa alamat IPv4 yang valid.',
    'ipv6'     => 'Bidang :attribute harus berupa alamat IPv6 yang valid.',
    'json'     => 'Bidang :attribute harus berupa JSON string yang valid.',
    'lt'       => [
        'numeric' => 'Bidang :attribute harus kurang dari :value.',
        'file'    => 'Bidang :attribute harus kurang dari :value kilobyte.',
        'string'  => 'Bidang :attribute harus kurang dari :value karakter.',
        'array'   => 'Bidang :attribute harus memiliki kurang dari :value item.',
    ],
    'lte' => [
        'numeric' => 'Bidang :attribute harus kurang dari atau sama dengan :value.',
        'file'    => 'Bidang :attribute harus kurang dari atau sama dengan :value kilobyte.',
        'string'  => 'Bidang :attribute harus kurang dari atau sama dengan :value karakter.',
        'array'   => 'Bidang :attribute tidak boleh memiliki lebih dari :value item.',
    ],
    'mac_address' => 'Bidang :attribute harus berupa alamat MAC yang valid.',
    'max'         => [
        'numeric' => 'Bidang :attribute tidak boleh lebih dari :max.',
        'file'    => 'Bidang :attribute tidak boleh lebih dari :max kilobyte.',
        'string'  => 'Bidang :attribute tidak boleh lebih dari :max karakter.',
        'array'   => 'Bidang :attribute tidak boleh memiliki lebih dari :max item.',
    ],
    'max_digits' => 'Bidang :attribute tidak boleh memiliki lebih dari :max digit.',
    'mimes'      => 'Bidang :attribute harus berupa berkas berjenis: :values.',
    'mimetypes'  => 'Bidang :attribute harus berupa berkas berjenis: :values.',
    'min'        => [
        'numeric' => 'Bidang :attribute harus minimal :min.',
        'file'    => 'Bidang :attribute harus minimal :min kilobyte.',
        'string'  => 'Bidang :attribute harus minimal :min karakter.',
        'array'   => 'Bidang :attribute harus memiliki minimal :min item.',
    ],
    'min_digits'       => 'Bidang :attribute harus memiliki setidaknya :min digit.',
    'multiple_of'      => 'Bidang :attribute harus merupakan kelipatan dari :value',
    'not_in'           => 'Bidang :attribute yang dipilih tidak valid.',
    'not_regex'        => 'Format :attribute tidak valid.',
    'numeric'          => 'Bidang :attribute harus berupa angka.',
    'password'         => [
        'letters'       => 'Bidang :attribute harus mengandung setidaknya satu huruf.',
        'mixed'         => 'Bidang :attribute harus mengandung setidaknya satu huruf besar dan satu huruf kecil.',
        'numbers'       => 'Bidang :attribute harus mengandung setidaknya satu angka.',
        'symbols'       => 'Bidang :attribute harus mengandung setidaknya satu simbol.',
        'uncompromised' => 'Bidang :attribute yang diberikan telah muncul dalam kebocoran data. Silakan pilih :attribute yang berbeda.',
    ],
    'present'          => 'Bidang :attribute harus ada.',
    'prohibited'       => 'Bidang :attribute dilarang.',
    'prohibited_if'    => 'Bidang :attribute dilarang bila :other adalah :value.',
    'prohibited_unless' => 'Bidang :attribute dilarang kecuali :other ada dalam :values.',
    'prohibits'        => 'Bidang :attribute melarang :other untuk ada.',
    'regex'            => 'Format :attribute tidak valid.',
    'required'         => 'Bidang :attribute wajib diisi.',
    'required_array_keys' => 'Bidang :attribute harus berisi entri untuk: :values.',
    'required_if'      => 'Bidang :attribute wajib diisi bila :other adalah :value.',
    'required_unless'  => 'Bidang :attribute wajib diisi kecuali :other memiliki nilai :values.',
    'required_with'    => 'Bidang :attribute wajib diisi bila terdapat :values.',
    'required_with_all' => 'Bidang :attribute wajib diisi bila terdapat :values.',
    'required_without' => 'Bidang :attribute wajib diisi bila tidak terdapat :values.',
    'required_without_all' => 'Bidang :attribute wajib diisi bila sama sekali tidak terdapat :values.',
    'same'             => 'Bidang :attribute dan :other harus sama.',
    'size'             => [
        'numeric' => 'Bidang :attribute harus berukuran :size.',
        'file'    => 'Bidang :attribute harus berukuran :size kilobyte.',
        'string'  => 'Bidang :attribute harus berukuran :size karakter.',
        'array'   => 'Bidang :attribute harus mengandung :size item.',
    ],
    'starts_with'      => 'Bidang :attribute harus dimulai dengan salah satu dari berikut ini: :values',
    'string'           => 'Bidang :attribute harus berupa string.',
    'timezone'         => 'Bidang :attribute harus berupa zona waktu yang valid.',
    'unique'           => 'Bidang :attribute sudah ada sebelumnya.',
    'uploaded'         => 'Bidang :attribute gagal diunggah.',
    'url'              => 'Format :attribute tidak valid.',
    'uuid'             => 'Bidang :attribute harus merupakan UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa Validasi Kustom
    |--------------------------------------------------------------------------
    |
    | Di sini Anda dapat menentukan pesan validasi kustom untuk atribut dengan menggunakan
    | konvensi "attribute.rule" untuk menamai baris. Hal ini membuat cepat untuk
    | menentukan baris bahasa kustom spesifik untuk aturan atribut yang diberikan.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Atribut Validasi Kustom
    |--------------------------------------------------------------------------
    |
    | Baris bahasa berikut digunakan untuk menukar placeholder atribut kami
    | dengan sesuatu yang lebih ramah pembaca seperti "Alamat Email" daripada
    | "email". Hal ini hanya membantu kami membuat pesan kami lebih ekspresif.
    |
    */

    'attributes' => [],

];
