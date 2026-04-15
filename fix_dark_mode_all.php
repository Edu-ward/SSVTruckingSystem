<?php
$directories = [__DIR__ . "/admin", __DIR__ . "/admin/views", __DIR__ . "/includes", __DIR__ . "/driver", __DIR__];

// The mappings of standard Tailwind classes to their dark mode equivalents
$classMappings = [
    // Backgrounds
    '/\bbg-white\b(?!.*dark:bg-)/' => 'bg-white dark:bg-gray-800',
    '/\bbg-gray-50\b(?!.*dark:bg-)/' => 'bg-gray-50 dark:bg-gray-900',
    '/\bbg-slate-900\b(?!.*dark:bg-)/' => 'bg-slate-900 dark:bg-gray-900', // for login page
    
    // Text colors
    '/\btext-gray-900\b(?!.*dark:text-)/' => 'text-gray-900 dark:text-gray-100',
    '/\btext-gray-800\b(?!.*dark:text-)/' => 'text-gray-800 dark:text-gray-200',
    '/\btext-gray-700\b(?!.*dark:text-)/' => 'text-gray-700 dark:text-gray-300',
    '/\btext-gray-600\b(?!.*dark:text-)/' => 'text-gray-600 dark:text-gray-400',
    '/\btext-slate-800\b(?!.*dark:text-)/' => 'text-slate-800 dark:text-gray-200', 
    '/\btext-slate-700\b(?!.*dark:text-)/' => 'text-slate-700 dark:text-gray-300',
    
    // Borders
    '/\bborder-gray-200\b(?!.*dark:border-)/' => 'border-gray-200 dark:border-gray-700',
    '/\bborder-gray-300\b(?!.*dark:border-)/' => 'border-gray-300 dark:border-gray-600',
    
    // Dividers
    '/\bdivide-gray-200\b(?!.*dark:divide-)/' => 'divide-gray-200 dark:divide-gray-700',
    
    // Hovers
    '/\bhover:bg-gray-50\b(?!.*dark:hover:bg-)/' => 'hover:bg-gray-50 dark:hover:bg-gray-700',
    '/\bhover:bg-gray-100\b(?!.*dark:hover:bg-)/' => 'hover:bg-gray-100 dark:hover:bg-gray-700',
];

$filesModified = 0;

foreach ($directories as $dir) {
    if (!is_dir($dir)) continue;

    $files = scandir($dir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $filePath = $dir . '/' . $file;
            $contents = file_get_contents($filePath);
            $newContents = $contents;
            
            // Only process class="..." attributes
            $newContents = preg_replace_callback('/class="([^"]+)"/', function($matches) use ($classMappings) {
                $classList = $matches[1];
                foreach ($classMappings as $pattern => $replacement) {
                    $classList = preg_replace($pattern, $replacement, $classList);
                }
                return 'class="' . $classList . '"';
            }, $newContents);

            if ($contents !== $newContents) {
                file_put_contents($filePath, $newContents);
                echo "Updated: $filePath\n";
                $filesModified++;
            }
        }
    }
}

echo "Total files updated: $filesModified\n";
?>
