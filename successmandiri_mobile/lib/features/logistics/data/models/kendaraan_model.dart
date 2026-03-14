import 'package:json_annotation/json_annotation.dart';

part 'kendaraan_model.g.dart';

@JsonSerializable()
class KendaraanModel {
  final int id;
  @JsonKey(name: 'no_polisi')
  final String noPolisi;
  @JsonKey(name: 'supir_id')
  final int supirId;
  @JsonKey(name: 'supir_nama')
  final String? supirNama;

  KendaraanModel({
    required this.id,
    required this.noPolisi,
    required this.supirId,
    this.supirNama,
  });

  factory KendaraanModel.fromJson(Map<String, dynamic> json) => _$KendaraanModelFromJson(json);
  Map<String, dynamic> toJson() => _$KendaraanModelToJson(this);
}
