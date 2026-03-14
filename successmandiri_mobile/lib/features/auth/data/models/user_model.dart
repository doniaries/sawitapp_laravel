import 'package:json_annotation/json_annotation.dart';

part 'user_model.g.dart';

@JsonSerializable()
class UserModel {
  final int id;
  final String name;
  final String email;
  @JsonKey(name: 'perusahaan_id')
  final int? perusahaanId;
  @JsonKey(name: 'perusahaan_nama')
  final String? perusahaanNama;
  @JsonKey(name: 'is_active')
  final bool isActive;
  final List<String> roles;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    this.perusahaanId,
    this.perusahaanNama,
    required this.isActive,
    required this.roles,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) => _$UserModelFromJson(json);
  Map<String, dynamic> toJson() => _$UserModelToJson(this);
}
