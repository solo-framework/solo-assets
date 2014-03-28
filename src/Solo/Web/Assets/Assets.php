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
	public $outdir = "/assets";

	public $ttl = 86400;

	public $files = array();

	/**
	 * Сборка файлов
	 *
	 * @return string
	 */
	public function bind()
	{
		try
		{
			$this->outdir = isset($params["outdir"]) ? $params["outdir"] : "/assets";

			$dr = $_SERVER["DOCUMENT_ROOT"];
			$outDir = "{$dr}{$this->outdir}";

			if (!isset($params["files"]))
				throw new \Exception ("Parameter 'files' is not defined");

			$files = explode(",", trim($params["files"], ","));
			$ttl = isset($params["ttl"]) ? intval($params["ttl"]) : 300;

			if (count($files) == 0)
				throw new \Exception ( "You have to set a file at least");

			$id = md5(str_replace(" ", "", $params["files"]));
			$outputFile = "{$outDir}/{$id}.js";
			$metaFile = "{$outDir}/{$id}.meta";

			$now = time();
			$hash = null;
			$storedHash = null;
			$age = 0;

			if (!is_file($metaFile) || $ttl == 0)
			{
				list($mtimes, $fileList) = $this->generateInfo($files);

				$this->writeMeta($metaFile, $mtimes, $now);
				$this->compileAssets($fileList, $outputFile);
				return "{$this->outdir}/{$id}.js?{$mtimes}";
			}
			else
			{
				$meta = file_get_contents($metaFile);

				list($storedHash, $age) = explode(":", $meta);
				if ((intval($age) + $ttl) < $now)
				{
					list($mtimes, $fileList) = $this->generateInfo($files);
					$this->writeMeta($metaFile, $mtimes, $now);

					if ($mtimes !== $storedHash)
					{
						$this->compileAssets($fileList, $outputFile);
						return "{$this->outdir}/{$id}.js?{$mtimes}";
					}
				}
				return "{$this->outdir}/{$id}.js?{$storedHash}";
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
