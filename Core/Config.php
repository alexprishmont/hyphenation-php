<?php


namespace Core;

use Exception;

class Config
{
    private $settings;
    private $path;

    public function __construct()
    {
        $this->path = dirname(__FILE__, 2) . "/Config/config.ini";
        $this->settings = parse_ini_file($this->path);
    }

    public function getConfigSettings(): array
    {
        return $this->settings;
    }

    public function writeConfigSettings(string $key, string $value): void
    {
        $this->settings[$key] = $value;
        $data = $this->refactorConfigArrayForSaving();

        try {
            $this->saveSettingsToIni($data);
        } catch (Exception $e) {
            print("\nError while saving config file! [$e]\n");
        }
    }

    private function refactorConfigArrayForSaving(): array
    {
        $data = [];
        foreach ($this->settings as $key => $val) {
            if (is_array($val)) {
                $data[] = "[$key]";
                foreach ($val as $skey => $sval) {
                    if (is_array($sval)) {
                        foreach ($sval as $_skey => $_sval) {
                            if (is_numeric($_skey))
                                $data[] = $skey . '[] = ' . (is_numeric($_sval) ? $_sval :
                                        (ctype_upper($_sval) ? $_sval : '"' . $_sval . '"'));
                            else
                                $data[] = $skey . '[' . $_skey . '] = ' . (is_numeric($_sval) ? $_sval :
                                        (ctype_upper($_sval) ? $_sval : '"' . $_sval . '"'));
                        }
                    } else {
                        $data[] = $skey . ' = ' . (is_numeric($sval) ? $sval :
                                (ctype_upper($sval) ? $sval : '"' . $sval . '"'));
                    }
                }
            } else {
                $data[] = $key . ' = ' . (is_numeric($val) ? $val : (ctype_upper($val) ? $val : '"' . $val . '"'));
            }
        }
        return $data;
    }

    private function saveSettingsToIni(array $data): bool
    {
        $file = fopen($this->path, 'w');
        $retries = 0;
        $maxRetries = 100;

        if (!$file)
            return false;

        do {
            if ($retries > 0)
                usleep(rand(1, 5000));

            $retries++;
        } while (!flock($file, LOCK_EX) && $retries <= $maxRetries);

        if ($retries == $maxRetries)
            return false;

        fwrite($file, implode(PHP_EOL, $data) . PHP_EOL);
        flock($file, LOCK_UN);
        fclose($file);

        return true;
    }
}