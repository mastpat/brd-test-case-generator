import 'package:file_picker/file_picker.dart';

class Requirement {
  final String projectTitle;
  final String requirementDescription;
  final String? changeRequest;
  final String priority;
  final DateTime? deliveryDate;
  final List<PlatformFile>? supportingFiles;

  Requirement({
    required this.projectTitle,
    required this.requirementDescription,
    this.changeRequest,
    required this.priority,
    this.deliveryDate,
    this.supportingFiles,
  });

  Map<String, dynamic> toJson() {
    return {
      'projectTitle': projectTitle,
      'requirementDescription': requirementDescription,
      'changeRequest': changeRequest,
      'priority': priority,
      'deliveryDate': deliveryDate?.toIso8601String(),
      'fileCount': supportingFiles?.length ?? 0,
    };
  }

  factory Requirement.fromJson(Map<String, dynamic> json) {
    return Requirement(
      projectTitle: json['projectTitle'] ?? '',
      requirementDescription: json['requirementDescription'] ?? '',
      changeRequest: json['changeRequest'],
      priority: json['priority'] ?? 'Medium',
      deliveryDate: json['deliveryDate'] != null 
          ? DateTime.parse(json['deliveryDate']) 
          : null,
    );
  }
}
