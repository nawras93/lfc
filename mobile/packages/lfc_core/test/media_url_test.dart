import 'package:flutter_test/flutter_test.dart';
import 'package:lfc_core/src/features/content/util/media_url.dart';

void main() {
  test('passes through absolute http and https URLs', () {
    expect(
      resolveMediaUrl(
        'https://www.lusailsc.qa/assets/images/news1.jpg',
        'http://10.0.2.2:8000/api/v1',
      ),
      'https://www.lusailsc.qa/assets/images/news1.jpg',
    );

    expect(
      resolveMediaUrl(
        'http://cdn.example.com/news.jpg',
        'http://10.0.2.2:8000/api/v1',
      ),
      'http://cdn.example.com/news.jpg',
    );
  });

  test('joins root-relative storage paths onto the api origin', () {
    expect(
      resolveMediaUrl(
        '/storage/news/example.jpg',
        'http://10.0.2.2:8000/api/v1',
      ),
      'http://10.0.2.2:8000/storage/news/example.jpg',
    );
  });

  test('respects the configured api base host', () {
    expect(
      resolveMediaUrl(
        '/storage/news/example.jpg',
        'http://192.168.1.20:8000/api/v1',
      ),
      'http://192.168.1.20:8000/storage/news/example.jpg',
    );
  });
}
