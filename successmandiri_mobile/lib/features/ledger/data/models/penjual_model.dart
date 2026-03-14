import 'package:json_annotation/json_annotation.dart';

part 'penjual_model.g.dart';

@JsonSerializable()
class PenjualModel {
  final int id;
  final String nama;
  final String? alamat;
  final String? telepon;
  final double hutang;
  @JsonKey(name: 'transaksi_count')
  final int? transaksiCount;

  PenjualModel({
    required this.id,
    required this.nama,
    this.alamat,
    this.telepon,
    required this.hutang,
    this.transaksiCount,
  });

  factory PenjualModel.fromJson(Map<String, dynamic> json) => _$PenjualModelFromJson(json);
  Map<String, dynamic> toJson() => _$PenjualModelToJson(this);
}
