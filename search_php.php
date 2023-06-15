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
    $phpGlob = $directory . DIRECTORY_SEPARATOR . $phpName;
    if ($phpBinaries = glob($phpGlob)) {
        return isPhpBinaries($phpBinaries);
    }
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        return getAllPhpExecFromDirWindows($directory, $phpName);   
    }
    return getAllPhpExecFromDirLinux($directory, $phpName);
}

function getAllPhpExecFromDirLinux($directory, $phpName) {
    $command = "ls -p \"$directory\" | grep -E \"$phpName\"";
    exec($command, $output);
    foreach ($output as $file) {
        $filePath = $directory . DIRECTORY_SEPARATOR . $file;
        if (isPhpBinary($filePath)) {
            return $filePath;
        }
    }
    return null;
}

function getAllPhpExecFromDirWindows($directory, $phpName) {
    $command = "dir /B \"$directory\" | findstr /R \"$phpName\"";
    exec($command, $output);
    foreach ($output as $file) {
        $filePath = $directory . DIRECTORY_SEPARATOR . $file;
        if (isPhpBinary($filePath)) {
            return $filePath;
        }
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
    // if (!isFileExecutable($binaryPath))
    //    return false;
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
