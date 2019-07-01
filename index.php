<?php

$filename='main.pdf';
function split_pdf($filename, $end_directory = false)
{
	require_once('fpdf/fpdf.php');
	require_once('fpdi/fpdi.php');
	
	$end_directory = $end_directory ? $end_directory : './';
	$new_path = preg_replace('/[\/]+/', '/', $end_directory.'/'.substr($filename, 0, strrpos($filename, '/')));
	
	if (!is_dir($new_path))
	{
		// Will make directories under end directory that don't exist
		// Provided that end directory exists and has the right permissions
		mkdir($new_path, 0777, true);
	}
	
	$pdf = new FPDI();
	$pagecount = $pdf->setSourceFile($filename); // How many pages?
	
	// Split each page into a new PDF
	for ($i = 1; $i <= $pagecount; $i+=2) {
		$new_pdf = new FPDI();
		$new_pdf->AddPage();
		$new_pdf->setSourceFile($filename);
		$new_pdf->useTemplate($new_pdf->importPage($i));
		if($i==$pagecount)	
		{
			continue;
		}
		else
		{
			$new_pdf->AddPage();
			$new_pdf->useTemplate($new_pdf->importPage($i+1));
		}
		
		try {
			$new_filename = $end_directory.str_replace('.pdf', '', $filename).'_'.$i.".pdf";
			$new_pdf->Output($new_filename, "F");
			echo "Page ".$i." split into ".$new_filename."<br />\n";
		} catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}
}
// Create and check permissions on end directory!
split_pdf("main.pdf", 'split/');

$rootPath = realpath('split');

// Initialize archive object
$zip = new ZipArchive();
$zip->open('file.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file)
{
    // Skip directories (they would be added automatically)
    if (!$file->isDir())
    {
        // Get real and relative path for current file
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);

        // Add current file to archive
        $zip->addFile($filePath, $relativePath);
    }
}

// Zip archive will be created only after closing object
$zip->close();
?>