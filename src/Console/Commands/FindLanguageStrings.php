<?php

namespace Matsevh\LanguageFinder\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FindLanguageStrings extends Command
{
  protected $signature = 'language:find {lang?}';
  protected $description = 'Find and extract translation strings into language files';

  public function handle()
  {
    $useShortKeys = config('language-finder.use_short_keys', false);
    $languageCode = $this->argument('lang');

    if ($languageCode && !preg_match('/^[a-z]{2}$/', $languageCode)) {
      $this->error('Invalid language code. Use a two-letter code (e.g., en, fr).');
      return;
    }

    $newTranslations = $this->scanProjectFiles();

    if ($languageCode) {
      $this->updateTranslationFile($languageCode, $newTranslations, $useShortKeys);
    } else {
      $this->updateAllTranslationFiles($newTranslations, $useShortKeys);
    }

    $this->info("Translations updated successfully.");
  }

  private function scanProjectFiles()
  {
    $files = File::allFiles(base_path());
    $strings = [];

    foreach ($files as $file) {
      if ($file->getExtension() !== 'php') continue;
      preg_match_all("/__\\(['\"](.*?)['\"]\\)/", File::get($file), $matches);
      $strings = array_merge($strings, $matches[1]);
    }

    return array_unique($strings);
  }

  private function updateTranslationFile($languageCode, array $translations, $useShortKeys)
  {
    if ($useShortKeys) {
      $filePath = resource_path("lang/{$languageCode}/auto_finder.php");
      $existing = File::exists($filePath) ? include $filePath : [];
      $merged = array_merge($existing, array_fill_keys($translations, ''));
      File::put($filePath, "<?php\n\nreturn " . var_export($merged, true) . ";\n");
    } else {
      $filePath = resource_path("lang/{$languageCode}.json");
      $existing = File::exists($filePath) ? json_decode(File::get($filePath), true) : [];
      $merged = array_merge($existing, array_fill_keys($translations, ''));
      File::put($filePath, json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
  }

  private function updateAllTranslationFiles(array $translations, $useShortKeys)
  {
    $langPath = resource_path('lang');
    $files = File::directories($langPath);

    foreach ($files as $file) {
      $languageCode = basename($file);
      $this->updateTranslationFile($languageCode, $translations, $useShortKeys);
    }
  }
}
