<?php

declare(strict_types=1);

/*
 * Stub class Redis untuk lingkungan tanpa ekstensi `phpredis`
 * (mis. dev di Windows tanpa redis ext, atau CI minimal). Tujuan kelas
 * ini adalah mencegah error "Class \"Redis\" not found" / "Undefined
 * constant Redis::OPT_*" ketika kode pihak ketiga (Spatie Permission,
 * Filament, Laravel Redis manager) yang sudah meng-cache instance
 * `RedisStore` mencoba memanggil `new Redis` atau setOption.
 *
 * Kelas ini idempoten: hanya didaftarkan bila kelas Redis benar-benar
 * tidak tersedia. Pada production yang memasang ekstensi `phpredis`
 * atau paket `predis/predis`, kelas ini tidak digunakan.
 */
if (! class_exists(\Redis::class, false)) {
    class Redis
    {
        // Konstanta yang biasa dipakai oleh PhpRedisConnector Laravel.
        public const OPT_PREFIX = 2;
        public const OPT_READ_TIMEOUT = 3;
        public const OPT_SCAN = 4;
        public const OPT_SERIALIZER = 1;
        public const OPT_BACKOFF_ALGORITHM = 5;
        public const OPT_BACKOFF_BASE = 6;
        public const OPT_BACKOFF_CAP = 7;
        public const OPT_TCP_KEEPALIVE = 8;
        public const OPT_REPLY_LITERAL = 9;
        public const OPT_NULL_MULTIBULK_AS_NULL = 10;
        public const OPT_MAX_RETRIES = 11;
        public const OPT_COMPRESSION = 12;
        public const OPT_COMPRESSION_LEVEL = 13;
        public const SERIALIZER_NONE = 0;
        public const SERIALIZER_PHP = 1;
        public const SERIALIZER_IGBINARY = 2;
        public const SERIALIZER_MSGPACK = 3;
        public const SERIALIZER_JSON = 4;
        public const SCAN_NORETRY = 0;
        public const SCAN_RETRY = 1;
        public const SCAN_PREFIX = 2;
        public const SCAN_NOPREFIX = 3;
        public const COMPRESSION_NONE = 0;
        public const COMPRESSION_LZF = 1;
        public const COMPRESSION_ZSTD = 2;
        public const COMPRESSION_LZ4 = 3;
        public const BACKOFF_ALGORITHM_DEFAULT = 0;
        public const BACKOFF_ALGORITHM_DECORRELATED_JITTER = 1;
        public const BACKOFF_ALGORITHM_FULL_JITTER = 2;
        public const BACKOFF_ALGORITHM_EQUAL_JITTER = 3;
        public const BACKOFF_ALGORITHM_EXPONENTIAL = 4;
        public const BACKOFF_ALGORITHM_UNIFORM = 5;
        public const BACKOFF_ALGORITHM_CONSTANT = 6;

        public function __construct()
        {
        }

        public function __call(string $method, array $args): mixed
        {
            return null;
        }

        public static function __callStatic(string $method, array $args): mixed
        {
            return null;
        }

        public function connect(...$args): bool
        {
            return true;
        }
        public function pconnect(...$args): bool
        {
            return true;
        }
        public function setOption(...$args): bool
        {
            return true;
        }
        public function getOption(...$args): mixed
        {
            return null;
        }
        public function auth(...$args): bool
        {
            return true;
        }
        public function select(int $db): bool
        {
            return true;
        }
        public function client(...$args): mixed
        {
            return null;
        }
        public function ping(...$args): mixed
        {
            return '+PONG';
        }
        public function close(): bool
        {
            return true;
        }
    }
}
