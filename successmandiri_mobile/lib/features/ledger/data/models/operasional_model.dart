import 'package:json_annotation/json_annotation.dart';

part 'operasional_model.g.dart';

@JsonSerializable()
class OperasionalModel {
  final int id;
  final DateTime tanggal;
  final String operasional;
  final String? kategori;
  @JsonKey(name: 'kategori_label')
  final String? kategoriLabel;
  @JsonKey(name: 'tipe_nama')
  final String? tipeNama;
  @JsonKey(name: 'nama_terkait')
  final String? namaTerkait;
  final double nominal;
  final String? keterangan;

  OperasionalModel({
    required this.id,
    required this.tanggal,
    required this.operasional,
    this.kategori,
    this.kategoriLabel,
    this.tipeNama,
    this.namaTerkait,
    required this.nominal,
    this.keterangan,
  });

  factory OperasionalModel.fromJson(Map<String, dynamic> json) => _$OperasionalModelFromJson(json);
  Map<String, dynamic> toJson() => _$OperasionalModelToJson(this);
}
