param(
	[string] $Source = 'base\OMAR PERFUMES LOGOS-02.png'
)

Add-Type -AssemblyName System.Drawing

$sourcePath = (Resolve-Path -LiteralPath $Source).Path
$sourceImage = [System.Drawing.Bitmap]::FromFile($sourcePath)

try {
	$left = $sourceImage.Width
	$top = $sourceImage.Height
	$right = -1
	$bottom = -1

	for ($y = 0; $y -lt $sourceImage.Height; $y++) {
		for ($x = 0; $x -lt $sourceImage.Width; $x++) {
			if ($sourceImage.GetPixel($x, $y).A -gt 4) {
				$left = [Math]::Min($left, $x)
				$top = [Math]::Min($top, $y)
				$right = [Math]::Max($right, $x)
				$bottom = [Math]::Max($bottom, $y)
			}
		}
	}

	if ($right -lt $left -or $bottom -lt $top) {
		throw 'The source logo has no visible pixels.'
	}

	$padding = 12
	$left = [Math]::Max(0, $left - $padding)
	$top = [Math]::Max(0, $top - $padding)
	$right = [Math]::Min($sourceImage.Width - 1, $right + $padding)
	$bottom = [Math]::Min($sourceImage.Height - 1, $bottom + $padding)
	$cropWidth = $right - $left + 1
	$cropHeight = $bottom - $top + 1
	$targetWidth = [Math]::Min(900, $cropWidth)
	$targetHeight = [Math]::Max(1, [int][Math]::Round($cropHeight * $targetWidth / $cropWidth))

	$resized = New-Object System.Drawing.Bitmap($targetWidth, $targetHeight)
	try {
		$graphics = [System.Drawing.Graphics]::FromImage($resized)
		try {
			$graphics.Clear([System.Drawing.Color]::Transparent)
			$graphics.CompositingMode = [System.Drawing.Drawing2D.CompositingMode]::SourceCopy
			$graphics.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
			$graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
			$graphics.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
			$destination = New-Object System.Drawing.Rectangle(0, 0, $targetWidth, $targetHeight)
			$sourceRect = New-Object System.Drawing.Rectangle($left, $top, $cropWidth, $cropHeight)
			$graphics.DrawImage($sourceImage, $destination, $sourceRect, [System.Drawing.GraphicsUnit]::Pixel)
		} finally {
			$graphics.Dispose()
		}

		$variants = @(
			@{ Name = 'omar-logo-light-v1.png'; Color = [System.Drawing.Color]::FromArgb(255, 255, 255) },
			@{ Name = 'omar-logo-dark-v1.png'; Color = [System.Drawing.Color]::FromArgb(7, 21, 39) }
		)

		foreach ($variant in $variants) {
			$bitmap = New-Object System.Drawing.Bitmap($targetWidth, $targetHeight)
			try {
				for ($y = 0; $y -lt $targetHeight; $y++) {
					for ($x = 0; $x -lt $targetWidth; $x++) {
						$alpha = $resized.GetPixel($x, $y).A
						$bitmap.SetPixel($x, $y, [System.Drawing.Color]::FromArgb($alpha, $variant.Color.R, $variant.Color.G, $variant.Color.B))
					}
				}

				foreach ($directory in @('assets', 'theme\omar-perfumes\assets')) {
					$targetDirectory = [System.IO.Path]::GetFullPath((Join-Path (Get-Location).Path $directory))
					[System.IO.Directory]::CreateDirectory($targetDirectory) | Out-Null
					$targetPath = [System.IO.Path]::Combine($targetDirectory, $variant.Name)
					$stream = New-Object System.IO.MemoryStream
					try {
						$bitmap.Save($stream, [System.Drawing.Imaging.ImageFormat]::Png)
						[System.IO.File]::WriteAllBytes($targetPath, $stream.ToArray())
					} finally {
						$stream.Dispose()
					}
				}
			} finally {
				$bitmap.Dispose()
			}
		}

		Write-Output "Created logo variants at ${targetWidth}x${targetHeight}px from crop ${cropWidth}x${cropHeight}px."
	} finally {
		$resized.Dispose()
	}
} finally {
	$sourceImage.Dispose()
}
