import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:file_picker/file_picker.dart';
import '../models/requirement.dart';

class ApiService {
  static const String baseUrl = 'http://localhost:5000';
  
  Future<Map<String, dynamic>> submitRequirement(Requirement requirement) async {
    try {
      var request = http.MultipartRequest(
        'POST',
        Uri.parse('$baseUrl/api/requirements.php'),
      );

      // Add text fields
      request.fields['projectTitle'] = requirement.projectTitle;
      request.fields['requirementDesc'] = requirement.requirementDescription;
      request.fields['changeRequest'] = requirement.changeRequest ?? '';
      request.fields['priority'] = requirement.priority;
      if (requirement.deliveryDate != null) {
        request.fields['deliveryDate'] = requirement.deliveryDate!.toIso8601String();
      }

      // Add files
      if (requirement.supportingFiles != null) {
        for (var file in requirement.supportingFiles!) {
          if (file.bytes != null) {
            request.files.add(
              http.MultipartFile.fromBytes(
                'supportingDocs[]',
                file.bytes!,
                filename: file.name,
              ),
            );
          }
        }
      }

      var streamedResponse = await request.send();
      var response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw Exception('HTTP ${response.statusCode}: ${response.reasonPhrase}');
      }
    } catch (e) {
      throw Exception('Network error: ${e.toString()}');
    }
  }

  Future<List<Map<String, dynamic>>> getGeneratedDocuments() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/requirements.php'),
        headers: {'Content-Type': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success']) {
          return List<Map<String, dynamic>>.from(data['documents']);
        } else {
          throw Exception(data['message'] ?? 'Failed to load documents');
        }
      } else {
        throw Exception('HTTP ${response.statusCode}: ${response.reasonPhrase}');
      }
    } catch (e) {
      throw Exception('Network error: ${e.toString()}');
    }
  }

  Future<bool> downloadDocument(int documentId, String type, String format) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/download.php?id=$documentId&type=$type&format=$format'),
      );

      if (response.statusCode == 200) {
        // In a real app, you would handle file download here
        // For web, you might use dart:html to trigger download
        // For mobile, you might save to device storage
        return true;
      } else {
        throw Exception('HTTP ${response.statusCode}: ${response.reasonPhrase}');
      }
    } catch (e) {
      throw Exception('Download error: ${e.toString()}');
    }
  }

  Future<Map<String, dynamic>> generateBRD(int requirementId) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/generate_brd.php'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({'requirement_id': requirementId}),
      );

      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw Exception('HTTP ${response.statusCode}: ${response.reasonPhrase}');
      }
    } catch (e) {
      throw Exception('BRD generation error: ${e.toString()}');
    }
  }

  Future<Map<String, dynamic>> generateUAT(int requirementId) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/generate_uat.php'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({'requirement_id': requirementId}),
      );

      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw Exception('HTTP ${response.statusCode}: ${response.reasonPhrase}');
      }
    } catch (e) {
      throw Exception('UAT generation error: ${e.toString()}');
    }
  }
}
