import 'package:json_annotation/json_annotation.dart';
import 'package:successmandiri_mobile/features/ledger/data/models/penjual_model.dart';

part 'transaksi_do_model.g.dart';

@JsonSerializable()
class TransaksiDoModel {
  final int id;
  final String nomor;
  final DateTime tanggal;
  final PenjualModel? penjual;
  @JsonKey(name: 'supir_nama')
  final String? supirNama;
  @JsonKey(name: 'kendaraan_plat')
  final String? kendaraanPlat;
  final double tonase;
  @JsonKey(name: 'harga_satuan')
  final double hargaSatuan;
  @JsonKey(name: 'sub_total')
  final double subTotal;
  @JsonKey(name: 'upah_bongkar')
  final double upahBongkar;
  @JsonKey(name: 'biaya_lain')
  final double biayaLain;
  @JsonKey(name: 'sisa_hutang_penjual')
  final double sisaHutangPenjual;
  @JsonKey(name: 'sisa_bayar')
  final double sisaBayar;
  @JsonKey(name: 'cara_bayar')
  final String caraBayar;

  TransaksiDoModel({
    required this.id,
    required this.nomor,
    required this.tanggal,
    this.penjual,
    this.supirNama,
    this.kendaraanPlat,
    required this.tonase,
    required this.hargaSatuan,
    required this.subTotal,
    required this.upahBongkar,
    required this.biayaLain,
    required this.sisaHutangPenjual,
    required this.sisaBayar,
    required this.caraBayar,
  });

  factory TransaksiDoModel.fromJson(Map<String, dynamic> json) => _$TransaksiDoModelFromJson(json);
  Map<String, dynamic> toJson() => _$TransaksiDoModelToJson(this);
}
