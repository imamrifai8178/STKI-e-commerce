<?php

namespace App\Services;

class PreprocessingService
{
    /**
     * STOPWORDS (SAFE VERSION - tidak buang kata penting IR)
     */
    private array $stopwords = [
        'yang', 'dan', 'di', 'ke', 'dari', 'ini', 'itu', 'dengan', 'untuk',
        'pada', 'adalah', 'dalam', 'tidak', 'juga', 'akan', 'ada', 'oleh',
        'atau', 'bisa', 'sudah', 'telah', 'saat', 'karena', 'tersebut',
        'sehingga', 'sebagai', 'namun', 'setelah', 'sebelum',
        'antara', 'kepada', 'bahwa', 'lebih', 'sangat', 'seperti', 'agar',
        'jika', 'bila', 'maka', 'ia', 'kita', 'kami', 'mereka',
        'saya', 'anda', 'kamu', 'dia', 'kini', 'lagi', 'baru', 'pun',
        'masih', 'belum', 'hanya', 'saja', 'paling', 'cukup', 'terlalu',
        'sebuah', 'suatu', 'setiap', 'seluruh', 'semua', 'beberapa',
        'oleh', 'karena', 'sebab', 'supaya', 'hingga', 'ketika'
    ];

    /**
     * NORMALIZATION DICTIONARY
     */
    private array $normalizationDict = [
        'yg' => 'yang',
        'dgn' => 'dengan',
        'utk' => 'untuk',
        'krn' => 'karena',
        'tdk' => 'tidak',
        'sdh' => 'sudah',
        'blm' => 'belum',
        'jg'  => 'juga',
        'dll' => 'dan lain lain',
        'dst' => 'dan seterusnya',
        'pd'  => 'pada',
        'dr'  => 'dari',
        'sy'  => 'saya',
        'mrk' => 'mereka',
        'thn' => 'tahun',
        'bln' => 'bulan',
        'jt'  => 'juta',
        'rb'  => 'ribu',
    ];

    /**
     * PIPELINE UTAMA
     */
    public function preprocess(string $text): array
    {
        $original   = $text;
        $caseFolded = $this->caseFolding($text);
        $normalized = $this->normalize($caseFolded);
        $tokens     = $this->tokenize($normalized);
        $filtered   = $this->removeStopwords($tokens);
        $stemmed    = $this->stem($filtered);

        return [
            'original'       => $original,
            'case_folded'    => $caseFolded,
            'normalized'     => $normalized,
            'tokens'         => $tokens,
            'after_stopword' => $filtered,
            'stemmed'        => $stemmed,

            // 🔥 PENTING UNTUK SEARCH & TF-IDF
            'final_tokens'   => array_values(array_unique($stemmed)),
        ];
    }

    /**
     * CASE FOLDING
     */
    public function caseFolding(string $text): string
    {
        return strtolower(trim($text));
    }

    /**
     * NORMALIZATION
     */
    public function normalize(string $text): string
    {
        $text = strip_tags($text);
        $text = preg_replace('/https?:\/\/[^\s]+/', '', $text);
        $text = preg_replace('/\d+/', ' ', $text);
        $text = preg_replace('/[^a-z\s]/', ' ', $text);

        $words = explode(' ', $text);

        $words = array_map(function ($word) {
            return $this->normalizationDict[$word] ?? $word;
        }, $words);

        return preg_replace('/\s+/', ' ', implode(' ', $words));
    }

    /**
     * TOKENIZATION
     */
    public function tokenize(string $text): array
    {
        $tokens = explode(' ', trim($text));

        return array_values(array_filter($tokens, function ($t) {
            return strlen(trim($t)) >= 2;
        }));
    }

    /**
     * STOPWORD REMOVAL
     */
    public function removeStopwords(array $tokens): array
    {
        return array_values(array_filter($tokens, function ($t) {
            return !in_array($t, $this->stopwords);
        }));
    }

    /**
     * STEMMING (AMAN)
     */
    public function stem(array $tokens): array
    {
        if (class_exists('\Sastrawi\Stemmer\StemmerFactory')) {
            $factory = new \Sastrawi\Stemmer\StemmerFactory();
            $stemmer = $factory->createStemmer();

            return array_map(fn($t) => $stemmer->stem($t), $tokens);
        }

        // fallback aman (tidak merusak kata)
        return $tokens;
    }

    /**
     * FINAL TOKENS (UNTUK TF-IDF / SEARCH)
     */
    public function getTokens(string $text): array
    {
        return $this->preprocess($text)['final_tokens'];
    }

    /**
     * STOPWORDS LIST
     */
    public function getStopwords(): array
    {
        return $this->stopwords;
    }
}