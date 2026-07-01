<?php

/**
 * Generator manual autoload_static.php + autoload_files.php
 * untuk lingkungan dev di mana `composer dump-autoload` crash
 * (issue Composer 2.4.1 di Windows tertentu — STATUS_STACK_BUFFER_OVERRUN).
 *
 * Skrip ini cukup untuk membuat boot Laravel dapat berjalan kembali
 * (autoload PSR-4 + classmap minimal + files). Setelah `composer install`
 * versi composer terbaru atau ekstensi PHP yang stabil, file ini dapat
 * digantikan kembali oleh output composer normal.
 */

$root = str_replace('\\', '/', __DIR__);
$composerLock = json_decode(file_get_contents("$root/composer.lock"), true);
$composerJson = json_decode(file_get_contents("$root/composer.json"), true);
$installedJson = json_decode(file_get_contents("$root/vendor/composer/installed.json"), true);

$prefixesPsr4 = [];
$fallbacksPsr4 = [];
$prefixes = []; // PSR-0 (rare)
$classMap = [];
$files = [];

$collectPackage = function (array $pkg, bool $isDev = false) use (&$prefixesPsr4, &$fallbacksPsr4, &$prefixes, &$classMap, &$files, $root) {
    $name = $pkg['name'] ?? null;
    if (! $name) {
        return;
    }
    $packageDir = "vendor/$name";
    $absPackageDir = "$root/$packageDir";
    if (! is_dir($absPackageDir)) {
        return;
    }

    $autoloadDir = $pkg['target-dir'] ?? '';
    $autoload = $pkg['autoload'] ?? [];

    foreach (($autoload['psr-4'] ?? []) as $namespace => $paths) {
        if (! is_array($paths)) {
            $paths = [$paths];
        }
        foreach ($paths as $p) {
            $rel = trim($p, '/');
            $abs = $absPackageDir . ($rel === '' ? '' : "/$rel");
            if (! is_dir($abs)) {
                continue;
            }
            $prefixesPsr4[$namespace][] = $abs;
        }
    }

    foreach (($autoload['psr-0'] ?? []) as $namespace => $paths) {
        if (! is_array($paths)) {
            $paths = [$paths];
        }
        foreach ($paths as $p) {
            $rel = trim($p, '/');
            $abs = $absPackageDir . ($rel === '' ? '' : "/$rel");
            if (! is_dir($abs)) {
                continue;
            }
            if ($namespace === '') {
                $fallbacksPsr4[] = $abs;
                continue;
            }
            $first = $namespace[0];
            $prefixes[$first][$namespace][] = $abs;
        }
    }

    foreach (($autoload['classmap'] ?? []) as $cmap) {
        $abs = "$absPackageDir/" . trim($cmap, '/');
        if (is_file($abs)) {
            // single file — not a directory; skip simple
            continue;
        }
        if (! is_dir($abs)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($abs, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $classes = scanFileForClasses($file->getPathname());
                foreach ($classes as $class) {
                    $classMap[$class] = $file->getPathname();
                }
            }
        }
    }

    foreach (($autoload['files'] ?? []) as $f) {
        $abs = "$absPackageDir/" . trim($f, '/');
        if (is_file($abs)) {
            $id = md5("$name:$f");
            $files[$id] = $abs;
        }
    }
};

function scanFileForClasses(string $path): array
{
    $contents = @file_get_contents($path);
    if ($contents === false) {
        return [];
    }
    $classes = [];
    if (preg_match_all('/^\s*(?:abstract\s+|final\s+|readonly\s+)*(?:class|interface|trait|enum)\s+(\w+)/m', $contents, $matches)) {
        $namespace = '';
        if (preg_match('/^\s*namespace\s+([^\s;{]+)/m', $contents, $nm)) {
            $namespace = $nm[1] . '\\';
        }
        foreach ($matches[1] as $name) {
            $classes[] = $namespace . $name;
        }
    }
    return $classes;
}

// Iterate installed packages
foreach (($installedJson['packages'] ?? []) as $pkg) {
    $collectPackage($pkg, $pkg['dev-requirement'] ?? false);
}

// Root package autoload
$rootAutoload = $composerJson['autoload'] ?? [];
$rootAutoloadDev = $composerJson['autoload-dev'] ?? [];

foreach (($rootAutoload['psr-4'] ?? []) as $namespace => $paths) {
    if (! is_array($paths)) {
        $paths = [$paths];
    }
    foreach ($paths as $p) {
        $rel = trim($p, '/');
        $abs = "$root/$rel";
        if (! is_dir($abs)) {
            continue;
        }
        $prefixesPsr4[$namespace][] = $abs;
    }
}

foreach (($rootAutoloadDev['psr-4'] ?? []) as $namespace => $paths) {
    if (! is_array($paths)) {
        $paths = [$paths];
    }
    foreach ($paths as $p) {
        $rel = trim($p, '/');
        $abs = "$root/$rel";
        if (! is_dir($abs)) {
            continue;
        }
        $prefixesPsr4[$namespace][] = $abs;
    }
}

foreach (($rootAutoloadDev['files'] ?? []) as $f) {
    $abs = "$root/" . trim($f, '/');
    if (is_file($abs)) {
        $id = md5("__root_dev__:$f");
        $files[$id] = $abs;
    }
}

// Inject RedisStub.php sebagai files autoload global (di samping autoload-dev)
// agar artisan/queue worker dev tanpa ekstensi `phpredis` tidak crash.
// Stub idempoten: hanya aktif jika kelas Redis belum tersedia.
$stubAbs = "$root/tests/stubs/RedisStub.php";
if (is_file($stubAbs)) {
    $files[md5('__redis_stub__')] = $stubAbs;
}

// Composer\InstalledVersions selalu dibutuhkan paket (Filament, Laravel)
// untuk introspeksi versi runtime. File ini biasanya autoloaded oleh
// composer normal, tetapi generator manual kita harus menambahkannya
// secara eksplisit ke classmap karena ada di luar struktur PSR-4.
$composerInstalledVersions = "$root/vendor/composer/InstalledVersions.php";
if (is_file($composerInstalledVersions)) {
    $classMap['Composer\\InstalledVersions'] = $composerInstalledVersions;
}
$composerClassLoader = "$root/vendor/composer/ClassLoader.php";
if (is_file($composerClassLoader)) {
    $classMap['Composer\\Autoload\\ClassLoader'] = $composerClassLoader;
}

// Sort prefixes by length descending (longer prefixes first for correct match)
uksort($prefixesPsr4, fn ($a, $b) => strlen($b) - strlen($a));

// Generate autoload_static.php
$out = "<?php\n\n// autoload_static.php @manually-generated\n\nnamespace Composer\\Autoload;\n\nclass ComposerStaticInite5fbbe6137dc77bcd5b6646b5dd75b52\n{\n";

// Files
$out .= "    public static \$files = array(\n";
foreach ($files as $id => $path) {
    $relPath = str_replace($root . '/', '', str_replace('\\', '/', $path));
    if (str_starts_with($relPath, 'vendor/')) {
        $rel = substr($relPath, 7); // strip 'vendor/'
        $out .= "        '$id' => __DIR__ . '/..' . '/$rel',\n";
    } else {
        $out .= "        '$id' => __DIR__ . '/../..' . '/$relPath',\n";
    }
}
$out .= "    );\n\n";

// PSR-4 prefix lengths
$out .= "    public static \$prefixLengthsPsr4 = array(\n";
$grouped = [];
foreach ($prefixesPsr4 as $ns => $_) {
    $first = $ns[0] ?? '';
    if ($first === '') {
        continue;
    }
    $grouped[$first][$ns] = strlen($ns);
}
foreach ($grouped as $first => $entries) {
    $out .= "        '$first' => array(\n";
    foreach ($entries as $ns => $len) {
        $escNs = addslashes($ns);
        $out .= "            '$escNs' => $len,\n";
    }
    $out .= "        ),\n";
}
$out .= "    );\n\n";

// PSR-4 dirs
$out .= "    public static \$prefixDirsPsr4 = array(\n";
foreach ($prefixesPsr4 as $ns => $dirs) {
    $escNs = addslashes($ns);
    $out .= "        '$escNs' => array(\n";
    $i = 0;
    foreach ($dirs as $d) {
        $relPath = str_replace($root . '/', '', str_replace('\\', '/', $d));
        if (str_starts_with($relPath, 'vendor/')) {
            $rel = substr($relPath, 7);
            $out .= "            $i => __DIR__ . '/..' . '/$rel',\n";
        } else {
            $out .= "            $i => __DIR__ . '/../..' . '/$relPath',\n";
        }
        $i++;
    }
    $out .= "        ),\n";
}
$out .= "    );\n\n";

// Empty prefixes (PSR-0)
$out .= "    public static \$prefixesPsr0 = array(\n    );\n\n";

// ClassMap dari semua package
$out .= "    public static \$classMap = array(\n";
ksort($classMap);
foreach ($classMap as $class => $path) {
    $relPath = str_replace($root . '/', '', str_replace('\\', '/', $path));
    $escClass = addslashes($class);
    if (str_starts_with($relPath, 'vendor/')) {
        $rel = substr($relPath, 7);
        $out .= "        '$escClass' => __DIR__ . '/..' . '/$rel',\n";
    } else {
        $out .= "        '$escClass' => __DIR__ . '/../..' . '/$relPath',\n";
    }
}
$out .= "    );\n\n";

// Initializer
$out .= "    public static function getInitializer(\$loader)\n    {\n";
$out .= "        return \\Closure::bind(function () use (\$loader) {\n";
$out .= "            \$loader->prefixLengthsPsr4 = ComposerStaticInite5fbbe6137dc77bcd5b6646b5dd75b52::\$prefixLengthsPsr4;\n";
$out .= "            \$loader->prefixDirsPsr4 = ComposerStaticInite5fbbe6137dc77bcd5b6646b5dd75b52::\$prefixDirsPsr4;\n";
$out .= "            \$loader->prefixesPsr0 = ComposerStaticInite5fbbe6137dc77bcd5b6646b5dd75b52::\$prefixesPsr0;\n";
$out .= "            \$loader->classMap = ComposerStaticInite5fbbe6137dc77bcd5b6646b5dd75b52::\$classMap;\n";
$out .= "        }, null, ClassLoader::class);\n";
$out .= "    }\n";
$out .= "}\n";

file_put_contents("$root/vendor/composer/autoload_static.php", $out);

// Generate autoload_files.php (just for compat)
$ff = "<?php\n\n// autoload_files.php @manually-generated\n\n\$vendorDir = dirname(__DIR__);\n\$baseDir = dirname(\$vendorDir);\n\nreturn array(\n";
foreach ($files as $id => $path) {
    $relPath = str_replace($root . '/', '', str_replace('\\', '/', $path));
    if (str_starts_with($relPath, 'vendor/')) {
        $rel = substr($relPath, 7);
        $ff .= "    '$id' => \$vendorDir . '/$rel',\n";
    } else {
        $ff .= "    '$id' => \$baseDir . '/$relPath',\n";
    }
}
$ff .= ");\n";
file_put_contents("$root/vendor/composer/autoload_files.php", $ff);

echo 'Generated autoload_static.php with ' . count($prefixesPsr4) . ' PSR-4 prefixes and ' . count($files) . " files.\n";
