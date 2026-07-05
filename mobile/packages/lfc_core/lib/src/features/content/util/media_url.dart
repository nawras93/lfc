String resolveMediaUrl(String rawUrl, String apiBaseUrl) {
  if (rawUrl.startsWith('http://') || rawUrl.startsWith('https://')) {
    return rawUrl;
  }

  final apiUri = Uri.parse(apiBaseUrl);
  final port = apiUri.hasPort ? ':${apiUri.port}' : '';
  final path = rawUrl.startsWith('/') ? rawUrl : '/$rawUrl';

  return '${apiUri.scheme}://${apiUri.host}$port$path';
}
