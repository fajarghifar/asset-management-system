<?php

return [
    'accepted' => ':attribute wajib diterima.',
    'accepted_if' => ':attribute wajib diterima ketika :other bernilai :value.',
    'active_url' => ':attribute bukan URL yang valid.',
    'after' => ':attribute wajib berupa tanggal setelah :date.',
    'after_or_equal' => ':attribute wajib berupa tanggal setelah atau sama dengan :date.',
    'alpha' => ':attribute wajib hanya berisi huruf.',
    'alpha_dash' => ':attribute wajib hanya berisi huruf, angka, setrip, dan garis bawah.',
    'alpha_num' => ':attribute wajib hanya berisi huruf dan angka.',
    'array' => ':attribute wajib berupa array.',
    'ascii' => ':attribute wajib hanya berisi karakter alfanumerik byte tunggal dan simbol.',
    'before' => ':attribute wajib berupa tanggal sebelum :date.',
    'before_or_equal' => ':attribute wajib berupa tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => ':attribute wajib memiliki antara :min dan :max item.',
        'file' => ':attribute wajib berukuran antara :min dan :max kilobita.',
        'numeric' => ':attribute wajib bernilai antara :min dan :max.',
        'string' => ':attribute wajib memiliki antara :min dan :max karakter.',
    ],
    'boolean' => 'Bidang :attribute wajib bernilai benar atau salah.',
    'can' => 'Bidang :attribute berisi nilai yang tidak sah.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Kata sandi salah.',
    'date' => ':attribute bukan tanggal yang valid.',
    'date_equals' => ':attribute wajib berupa tanggal yang sama dengan :date.',
    'date_format' => ':attribute tidak cocok dengan format :format.',
    'decimal' => ':attribute wajib memiliki :decimal tempat desimal.',
    'declined' => ':attribute wajib ditolak.',
    'declined_if' => ':attribute wajib ditolak ketika :other bernilai :value.',
    'different' => ':attribute dan :other wajib berbeda.',
    'digits' => ':attribute wajib :digits digit.',
    'digits_between' => ':attribute wajib antara :min dan :max digit.',
    'dimensions' => ':attribute memiliki dimensi gambar yang tidak valid.',
    'distinct' => 'Bidang :attribute memiliki nilai duplikat.',
    'doesnt_end_with' => ':attribute tidak boleh diakhiri dengan salah satu dari berikut ini: :values.',
    'doesnt_start_with' => ':attribute tidak boleh diawali dengan salah satu dari berikut ini: :values.',
    'email' => ':attribute wajib berupa alamat email yang valid.',
    'ends_with' => ':attribute wajib diakhiri dengan salah satu dari berikut ini: :values.',
    'enum' => ':attribute yang dipilih tidak valid.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'extensions' => 'Bidang :attribute wajib memiliki salah satu ekstensi berikut: :values.',
    'file' => ':attribute wajib berupa berkas.',
    'filled' => 'Bidang :attribute wajib memiliki nilai.',
    'gt' => [
        'array' => ':attribute wajib memiliki lebih dari :value item.',
        'file' => ':attribute wajib berukuran lebih besar dari :value kilobita.',
        'numeric' => ':attribute wajib bernilai lebih besar dari :value.',
        'string' => ':attribute wajib memiliki lebih dari :value karakter.',
    ],
    'gte' => [
        'array' => ':attribute wajib memiliki :value item atau lebih.',
        'file' => ':attribute wajib berukuran lebih besar dari atau sama dengan :value kilobita.',
        'numeric' => ':attribute wajib bernilai lebih besar dari atau sama dengan :value.',
        'string' => ':attribute wajib memiliki lebih dari atau sama dengan :value karakter.',
    ],
    'hex_color' => 'Bidang :attribute wajib berupa warna heksadesimal yang valid.',
    'image' => ':attribute wajib berupa gambar.',
    'in' => ':attribute yang dipilih tidak valid.',
    'in_array' => 'Bidang :attribute tidak ada di :other.',
    'integer' => ':attribute wajib berupa bilangan bulat.',
    'ip' => ':attribute wajib berupa alamat IP yang valid.',
    'ipv4' => ':attribute wajib berupa alamat IPv4 yang valid.',
    'ipv6' => ':attribute wajib berupa alamat IPv6 yang valid.',
    'json' => ':attribute wajib berupa string JSON yang valid.',
    'list' => 'Bidang :attribute wajib berupa daftar.',
    'lowercase' => ':attribute wajib berupa huruf kecil.',
    'lt' => [
        'array' => ':attribute wajib memiliki kurang dari :value item.',
        'file' => ':attribute wajib berukuran kurang dari :value kilobita.',
        'numeric' => ':attribute wajib bernilai kurang dari :value.',
        'string' => ':attribute wajib memiliki kurang dari :value karakter.',
    ],
    'lte' => [
        'array' => ':attribute tidak boleh memiliki lebih dari :value item.',
        'file' => ':attribute wajib berukuran kurang dari atau sama dengan :value kilobita.',
        'numeric' => ':attribute wajib bernilai kurang dari atau sama dengan :value.',
        'string' => ':attribute wajib memiliki kurang dari atau sama dengan :value karakter.',
    ],
    'mac_address' => ':attribute wajib berupa alamat MAC yang valid.',
    'max' => [
        'array' => ':attribute tidak boleh memiliki lebih dari :max item.',
        'file' => ':attribute tidak boleh berukuran lebih besar dari :max kilobita.',
        'numeric' => ':attribute tidak boleh bernilai lebih besar dari :max.',
        'string' => ':attribute tidak boleh memiliki lebih dari :max karakter.',
    ],
    'max_digits' => ':attribute tidak boleh memiliki lebih dari :max digit.',
    'mimes' => ':attribute wajib berupa berkas berjenis: :values.',
    'mimetypes' => ':attribute wajib berupa berkas berjenis: :values.',
    'min' => [
        'array' => ':attribute wajib memiliki setidaknya :min item.',
        'file' => ':attribute wajib berukuran setidaknya :min kilobita.',
        'numeric' => ':attribute wajib bernilai setidaknya :min.',
        'string' => ':attribute wajib memiliki setidaknya :min karakter.',
    ],
    'min_digits' => ':attribute wajib memiliki setidaknya :min digit.',
    'missing' => 'Bidang :attribute wajib hilang.',
    'missing_if' => 'Bidang :attribute wajib hilang ketika :other bernilai :value.',
    'missing_unless' => 'Bidang :attribute wajib hilang kecuali :other bernilai :value.',
    'missing_with' => 'Bidang :attribute wajib hilang ketika :values ada.',
    'missing_with_all' => 'Bidang :attribute wajib hilang ketika :values ada.',
    'multiple_of' => ':attribute wajib merupakan kelipatan dari :value.',
    'not_in' => ':attribute yang dipilih tidak valid.',
    'not_regex' => 'Format :attribute tidak valid.',
    'numeric' => ':attribute wajib berupa angka.',
    'password' => [
        'letters' => ':attribute wajib mengandung setidaknya satu huruf.',
        'mixed' => ':attribute wajib mengandung setidaknya satu huruf besar dan satu huruf kecil.',
        'numbers' => ':attribute wajib mengandung setidaknya satu angka.',
        'symbols' => ':attribute wajib mengandung setidaknya satu simbol.',
        'uncompromised' => ':attribute yang diberikan telah muncul dalam kebocoran data. Silakan pilih :attribute yang berbeda.',
    ],
    'present' => 'Bidang :attribute wajib ada.',
    'present_if' => 'Bidang :attribute wajib ada ketika :other bernilai :value.',
    'present_unless' => 'Bidang :attribute wajib ada kecuali :other bernilai :value.',
    'present_with' => 'Bidang :attribute wajib ada ketika :values ada.',
    'present_with_all' => 'Bidang :attribute wajib ada ketika :values ada.',
    'prohibited' => 'Bidang :attribute dilarang.',
    'prohibited_if' => 'Bidang :attribute dilarang ketika :other bernilai :value.',
    'prohibited_unless' => 'Bidang :attribute dilarang kecuali :other ada di :values.',
    'prohibiteds' => 'Bidang :attribute dilarang.',
    'same' => ':attribute dan :other wajib cocok.',
    'size' => [
        'array' => ':attribute wajib berisi :size item.',
        'file' => ':attribute wajib berukuran :size kilobita.',
        'numeric' => ':attribute wajib bernilai :size.',
        'string' => ':attribute wajib berisi :size karakter.',
    ],
    'starts_with' => ':attribute wajib diawali dengan salah satu dari berikut ini: :values.',
    'string' => ':attribute wajib berupa string.',
    'timezone' => ':attribute wajib berupa zona waktu yang valid.',
    'unique' => ':attribute sudah ada sebelumnya.',
    'uploaded' => ':attribute gagal diunggah.',
    'uppercase' => ':attribute wajib berupa huruf besar.',
    'url' => ':attribute wajib berupa URL yang valid.',
    'ulid' => ':attribute wajib berupa ULID yang valid.',
    'uuid' => ':attribute wajib berupa UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
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
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'nama',
        'username' => 'nama pengguna',
        'email' => 'alamat email',
        'first_name' => 'nama depan',
        'last_name' => 'nama belakang',
        'password' => 'kata sandi',
        'password_confirmation' => 'konfirmasi kata sandi',
        'city' => 'kota',
        'country' => 'negara',
        'address' => 'alamat',
        'phone' => 'telepon',
        'mobile' => 'seluler',
        'age' => 'usia',
        'sex' => 'jenis kelamin',
        'gender' => 'jenis kelamin',
        'day' => 'hari',
        'month' => 'bulan',
        'year' => 'tahun',
        'hour' => 'jam',
        'minute' => 'menit',
        'second' => 'detik',
        'title' => 'judul',
        'content' => 'konten',
        'description' => 'deskripsi',
        'excerpt' => 'kutipan',
        'date' => 'tanggal',
        'time' => 'waktu',
        'available' => 'sedia',
        'size' => 'ukuran',
    ],

];
