import 'package:json_annotation/json_annotation.dart';

part 'supir_model.g.dart';

@JsonSerializable()
class SupirModel {
  final int id;
  final String nama;
  final String? alamat;
  final String? telepon;
  final double hutang;
  @JsonKey(name: 'kendaraan_count')
  final int? kendaraanCount;

  SupirModel({
    required this.id,
    required this.nama,
    this.alamat,
    this.telepon,
    required this.hutang,
    this.kendaraanCount,
  });

  factory SupirModel.fromJson(Map<String, dynamic> json) => _$SupirModelFromJson(json);
  Map<String, dynamic> toJson() => _$SupirModelToJson(this);
}
