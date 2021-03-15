<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
  <url><loc>{{ $home }}/</loc></url>
  @foreach ($urls as $url => $content)
  @if (empty($content['images']))
  <url><loc>{{ $url }}</loc></url>
  @else
  <url><loc>{{ $url }}</loc>
    @foreach ($content['images'] as $image => $title)
    <image:image><image:loc>{{ $image }}</image:loc></image:image>
    @endforeach
  </url>
  @endif
  @endforeach
  @foreach ($pdfs as $pdf => $name)
  <url><loc>{{ $pdf }}</loc></url>
  @endforeach
</urlset>
