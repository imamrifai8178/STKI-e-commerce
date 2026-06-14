<?php

namespace App\Services;

/**
 * PreprocessingService
 *
 * Layanan preprocessing teks Bahasa Indonesia untuk sistem IR.
 * Pipeline: Case Folding → Tokenization → Normalisasi → Stopword Removal → Stemming
 *
 * Menggunakan library Sastrawi untuk stemming Bahasa Indonesia.
 */
class PreprocessingService
{
    /**
     * Daftar stopword Bahasa Indonesia (komprehensif).
     * Mencakup kata-kata umum yang tidak membawa makna semantik.
     */
    private array $stopwords = [
        'yang', 'dan', 'di', 'ke', 'dari', 'ini', 'itu', 'dengan', 'untuk',
        'pada', 'adalah', 'dalam', 'tidak', 'juga', 'akan', 'ada', 'oleh',
        'atau', 'bisa', 'sudah', 'telah', 'saat', 'karena', 'tersebut',
        'sehingga', 'sebagai', 'namun', 'namun', 'setelah', 'sebelum',
        'antara', 'kepada', 'bahwa', 'lebih', 'sangat', 'seperti', 'agar',
        'jika', 'bila', 'maka', 'ia', 'kita', 'kami', 'mereka', 'mereka',
        'saya', 'anda', 'kamu', 'dia', 'kini', 'lagi', 'baru', 'pun',
        'masih', 'belum', 'hanya', 'saja', 'paling', 'cukup', 'terlalu',
        'sebuah', 'suatu', 'setiap', 'seluruh', 'semua', 'beberapa', 'para',
        'ber', 'me', 'ter', 'pe', 'per', 'se', 'di', 'ke', 'an', 'kan',
        'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan',
        'telah', 'sudah', 'pernah', 'sedang', 'sementara', 'lagi', 'hingga',
        'sampai', 'kemudian', 'lalu', 'melalui', 'secara', 'terhadap', 'hal',
        'bagi', 'saat', 'ketika', 'bagian', 'tapi', 'tetapi', 'walau',
        'meski', 'walaupun', 'meskipun', 'yaitu', 'yakni', 'antara', 'pihak',
        'demikian', 'tersebut', 'bukan', 'bukan', 'tanpa', 'atas', 'bawah',
        'depan', 'belakang', 'kiri', 'kanan', 'dalam', 'luar', 'sejak',
        'selama', 'setelah', 'sebelum', 'seraya', 'sembari', 'sedangkan',
        'adapun', 'apabila', 'asalkan', 'begitu', 'bahkan', 'apalagi',
        'padahal', 'kendati', 'sekalipun', 'jangankan', 'apalagi', 'lagi',
        'lagi', 'pula', 'juga', 'pun', 'baik', 'maupun', 'entah', 'atau',
        'tak', 'tiada', 'tiap', 'setiap', 'masing', 'serta', 'sambil',
        'supaya', 'agar', 'sehingga', 'akibat', 'oleh', 'karena', 'sebab',
        'maka', 'saat', 'tatkala', 'sewaktu', 'begitu', 'setelah', 'ketika',
        'jauh', 'dekat', 'besar', 'kecil', 'tinggi', 'rendah', 'panjang',
        'pendek', 'banyak', 'sedikit', 'sebagian', 'seluruh', 'semua',
    ];

    /**
     * Kamus normalisasi kata tidak baku ke baku.
     */
    private array $normalizationDict = [
        'yg'    => 'yang',
        'dgn'   => 'dengan',
        'utk'   => 'untuk',
        'krn'   => 'karena',
        'tdk'   => 'tidak',
        'dg'    => 'dengan',
        'sdh'   => 'sudah',
        'blm'   => 'belum',
        'jg'    => 'juga',
        'lg'    => 'lagi',
        'tsb'   => 'tersebut',
        'dll'   => 'dan lain lain',
        'dst'   => 'dan seterusnya',
        'pd'    => 'pada',
        'krna'  => 'karena',
        'bs'    => 'bisa',
        'mnrt'  => 'menurut',
        'dpt'   => 'dapat',
        'org'   => 'orang',
        'sbg'   => 'sebagai',
        'thd'   => 'terhadap',
        'dlm'   => 'dalam',
        'dr'    => 'dari',
        'sy'    => 'saya',
        'km'    => 'kami',
        'mrk'   => 'mereka',
        'stlh'  => 'setelah',
        'sblm'  => 'sebelum',
        'bln'   => 'bulan',
        'thn'   => 'tahun',
        'jt'    => 'juta',
        'rb'    => 'ribu',
        'miliar'=> 'miliar',
        'pct'   => 'persen',
        '%'     => 'persen',
    ];

    /**
     * Jalankan seluruh pipeline preprocessing dan kembalikan semua tahap.
     *
     * @param string $text Teks asli yang akan diproses
     * @return array Hasil setiap tahap preprocessing
     */
    public function preprocess(string $text): array
    {
        $original   = $text;
        $caseFolded = $this->caseFolding($text);
        $normalized = $this->normalize($caseFolded);
        $tokens     = $this->tokenize($normalized);
        $noStop     = $this->removeStopwords($tokens);
        $stemmed    = $this->stem($noStop);

        return [
            'original'         => $original,
            'case_folded'      => $caseFolded,
            'normalized'       => $normalized,
            'tokens'           => $tokens,
            'after_stopword'   => $noStop,
            'stemmed'          => $stemmed,
            'final_tokens'     => array_values(array_unique($stemmed)),
        ];
    }

    /**
     * Kembalikan hanya token akhir (setelah stemming).
     *
     * @param string $text Teks input
     * @return array Token hasil akhir preprocessing
     */
    public function getTokens(string $text): array
    {
        $result = $this->preprocess($text);
        return $result['stemmed'];
    }

    /**
     * TAHAP 1: Case Folding - ubah semua huruf menjadi huruf kecil.
     */
    public function caseFolding(string $text): string
    {
        return strtolower(trim($text));
    }

    /**
     * TAHAP 2: Normalisasi - hapus karakter tidak perlu, normalisasi singkatan.
     */
    public function normalize(string $text): string
    {
        // Hapus HTML tags
        $text = strip_tags($text);

        // Hapus URL
        $text = preg_replace('/https?:\/\/[^\s]+/', '', $text);

        // Hapus email
        $text = preg_replace('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/', '', $text);

        // Ganti angka dengan spasi
        $text = preg_replace('/\d+/', ' ', $text);

        // Hapus tanda baca kecuali spasi
        $text = preg_replace('/[^a-z\s]/', ' ', $text);

        // Normalisasi singkatan
        $words = explode(' ', $text);
        $normalized = array_map(function ($word) {
            return $this->normalizationDict[$word] ?? $word;
        }, $words);

        // Hapus spasi berlebih
        return preg_replace('/\s+/', ' ', implode(' ', $normalized));
    }

    /**
     * TAHAP 3: Tokenization - pecah teks menjadi array kata.
     */
    public function tokenize(string $text): array
    {
        $tokens = explode(' ', trim($text));
        // Hapus token kosong dan token terlalu pendek (< 2 karakter)
        return array_values(array_filter($tokens, function ($token) {
            return strlen(trim($token)) >= 2;
        }));
    }

    /**
     * TAHAP 4: Stopword Removal - hapus kata-kata umum Bahasa Indonesia.
     */
    public function removeStopwords(array $tokens): array
    {
        return array_values(array_filter($tokens, function ($token) {
            return !in_array(strtolower($token), $this->stopwords);
        }));
    }

    /**
     * TAHAP 5: Stemming - kembalikan kata ke bentuk dasar menggunakan Sastrawi.
     *
     * Fallback ke algoritma manual sederhana jika Sastrawi tidak tersedia.
     */
    public function stem(array $tokens): array
    {
        // Cek apakah library Sastrawi tersedia
        if (class_exists('\Sastrawi\Stemmer\StemmerFactory')) {
            $factory  = new \Sastrawi\Stemmer\StemmerFactory();
            $stemmer  = $factory->createStemmer();
            return array_map(fn($t) => $stemmer->stem($t), $tokens);
        }

        // Fallback: stemming sederhana dengan aturan afiks Bahasa Indonesia
        return array_map([$this, 'simpleStem'], $tokens);
    }

    /**
     * Algoritma stemming sederhana sebagai fallback.
     * Menghapus awalan (me-, ber-, pe-, ter-, ke-, se-) dan akhiran (-an, -kan, -i).
     *
     * @param string $word Kata yang akan di-stem
     * @return string Kata dasar
     */
    private function simpleStem(string $word): string
    {
        if (strlen($word) <= 3) return $word;

        // Hapus akhiran
        $suffixes = ['kan', 'an', 'i'];
        foreach ($suffixes as $suffix) {
            if (substr($word, -strlen($suffix)) === $suffix && strlen($word) > strlen($suffix) + 2) {
                $word = substr($word, 0, -strlen($suffix));
                break;
            }
        }

        // Hapus awalan
        $prefixes = ['menge', 'memper', 'diper', 'keter', 'meny', 'peny',
                     'mem', 'ber', 'ter', 'per', 'me', 'pe', 'ke', 'se', 'di'];
        foreach ($prefixes as $prefix) {
            if (substr($word, 0, strlen($prefix)) === $prefix && strlen($word) > strlen($prefix) + 2) {
                $word = substr($word, strlen($prefix));
                break;
            }
        }

        return $word;
    }

    /**
     * Kembalikan daftar stopwords yang digunakan.
     */
    public function getStopwords(): array
    {
        return $this->stopwords;
    }
}