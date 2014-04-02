<?php
/**
 * Сборка и сжатие нескольких Javascript или CSS файлов в один
 *
 * PHP version 5
 *
 * @package Solo\Web
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Web\Assets;

class Assets
{
	/**
	 * Каталог для комбинированных файлов
	 *
	 * @var string
	 */
	public $outdir = "/assets";

	/**
	 * время в секундах, через которое происходит
	 * проверка файлов на изменение
	 * (если 0 - проверка происходит при каждом запросе)
	 *
	 * @var int
	 */
	public $ttl = 86400;

	/**
	 *
	 * @var bool
	 */
	public $async = false;

//	public $minJS = false;

	public $debug = false;

	/**
	 * Сборка файлов
	 *
	 * @param string $files Список файлов через запятую
	 *
	 * @return string
	 */
	public function bind($files)
	{
		try
		{
			$files = str_replace(array(" ", "\t", "\n"), "", $files);
			$id = md5($files);
			$files = explode(",", trim($files, ","));

			if ($this->debug)
				return $this->generateDebugLink($files);

			$dr = $_SERVER["DOCUMENT_ROOT"];
			$outDir = "{$dr}{$this->outdir}";

			if (!is_dir($outDir))
				throw new \Exception("Asset directory doesn't exist {$outDir}");

			if (!$files)
				throw new \Exception ("Parameter 'files' is not defined");

			if (count($files) == 0)
				throw new \Exception ( "You have to set a file at least");


			$outputFile = "{$outDir}/{$id}.js";
			$metaFile = "{$outDir}/{$id}.meta";

			$now = time();
			$hash = null;
			$storedHash = null;

			if (!is_file($metaFile) || $this->ttl == 0)
			{
				list($mtimes, $fileList) = $this->generateInfo($files);

				$this->writeMeta($metaFile, $mtimes, $now);
				$this->compileAssets($fileList, $outputFile);
				return $this->generateLink($this->outdir, $id, $mtimes);
				//return "{$this->outdir}/{$id}.js?{$mtimes}";
			}
			else
			{
				$meta = file_get_contents($metaFile);

				list($storedHash, $age) = explode(":", $meta);
				if ((intval($age) + $this->ttl) < $now)
				{
					list($mtimes, $fileList) = $this->generateInfo($files);
					$this->writeMeta($metaFile, $mtimes, $now);

					if ($mtimes !== $storedHash)
					{
						$this->compileAssets($fileList, $outputFile);
						return "{$this->outdir}/{$id}.js?{$mtimes}";
					}
				}
				return $this->generateLink($this->outdir, $id, $storedHash);
				//return "{$this->outdir}/{$id}.js?{$storedHash}";
			}
		}
		catch (\Exception $e)
		{
			return "Assets error: " . $e->getMessage();
		}
	}

	function writeMeta($metaFile, $mtimes, $ts)
	{
		$res = @file_put_contents($metaFile, "{$mtimes}:{$ts}");
		if (!$res)
			throw new \Exception("Can't write meta file {$metaFile}");
	}

	protected function generateLink($outdir, $id, $timestamp)
	{
		$async = "";
		if ($this->async)
			$async = "async";
		return "<script type='text/javascript' src='{$outdir}/{$id}.js?{$timestamp}' {$async}></script>\n";
//		return "{$outdir}/{$id}.js?{$timestamp}";
	}


	protected function generateDebugLink(array $files)
	{
		$res = "";
		foreach ($files as $file)
		{
			$res .= "<script type='text/javascript' src='{$file}'></script>\n";
		}

		return $res;
	}



	/**
	 * @param $files
	 *
	 * @throws \Exception
	 * @return array
	 */
	protected function generateInfo($files)
	{
		$dr = $_SERVER["DOCUMENT_ROOT"];
		$mtimes = "";
		$fileList = array();

		foreach ($files as $file)
		{
			$file = $dr . trim($file);
			if (!is_file($file))
				throw new \Exception ( "File '{$file}' doesn't exist" );
			else
			{
				$mtimes .= filemtime($file);
				$fileList[] = $file;
			}
		}

		$mtimes = md5($mtimes);
		return array($mtimes, $fileList);
	}

	protected function compileAssets(array $fileNames, $outFile)
	{
		$fp = @fopen($outFile, "w+");
		if ($fp)
		{
			if (flock($fp, LOCK_EX))
			{
				foreach ($fileNames as $name)
				{
					$content = file_get_contents($name);
					if (!$content)
					{
						flock($fp, LOCK_UN);
						fclose($fp);
						throw new \Exception("Can't read a content of {$name}");
					}
					else
					{
						fwrite($fp, $content);
					}
				}
				flock($fp, LOCK_UN);
				return true;
			}
			else
			{
				throw new \Exception("Can't lock out file {$outFile}");
			}
		}
		else
		{
			throw new \Exception("Can't open out file {$outFile} ");
		}
	}
}

