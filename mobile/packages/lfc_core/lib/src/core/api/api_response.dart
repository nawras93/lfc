Map<String, dynamic> unwrapData(Map<String, dynamic> json) {
  final data = json['data'];

  if (data is Map<String, dynamic>) {
    return data;
  }

  return json;
}
