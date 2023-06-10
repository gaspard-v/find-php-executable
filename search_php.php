<?php
function findPhpBinary() {
    $phpNames = ["php", "php*"];
    // Vérifier si PHP_BINARY pointe vers le binaire PHP
    if (defined('PHP_BINARY') && isPhpBinary(PHP_BINARY)) {
        return PHP_BINARY;
    }

    foreach ($phpNames as $phpName) {
        if ($r = searchPath($phpName))
            return $r;
        if ($r = searchBinDir($phpName))
            return $r;
    }
    return null;
}

function searchPath($phpName) {
    $path = getenv('PATH');
    // Vérifier si le binaire est dans la variable d'environnement PATH
    $pathDirectories = explode(PATH_SEPARATOR, $path);
    foreach ($pathDirectories as $directory) {
        if($r = getAllPhpExecFromDir($directory, $phpName))
            return $r;
    }
    return null;
}

function searchBinDir($phpName) {
    // Rechercher les binaires PHP dans le répertoire PHP_BINDIR
    $phpBinDir = PHP_BINDIR;
    if($r = getAllPhpExecFromDir($phpBinDir, $phpName))
        return $r;
    return null;
}

function getAllPhpExecFromDir($directory, $phpName) {
    if($phpBinaries = glob($directory . DIRECTORY_SEPARATOR . $phpName)) {
        return isPhpBinaries($phpBinaries);
    }
    return null;
}

function isPhpBinaries($phpBinaries) {
    foreach ($phpBinaries as $phpBinary) {
        if (isPhpBinary($phpBinary)) {
            return $phpBinary;
        }
    }
    return null;
}

function isPhpBinary($binaryPath) {
    if (!isFileExecutable($binaryPath))
        return false;
    // Exécuter la commande "$binaryPath --version" et vérifier si la sortie contient "PHP"
    $command = $binaryPath . ' --version';
    $output = shell_exec($command);
    return (strpos($output, 'PHP') !== false) && (strpos($output, 'cli') !== false);
}

function isFileExecutable($filePath) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows
        return is_executable($filePath) && (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'exe');
    } else {
        // Linux et autres systèmes Unix
        return is_executable($filePath);
    }
}

echo findPhpBinary();
