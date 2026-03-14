import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:successmandiri_mobile/core/theme/app_theme.dart';

class LedgerPage extends StatelessWidget {
  const LedgerPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Supplier Ledger',
              style: Theme.of(context).textTheme.headlineSmall,
            ),
            const SizedBox(height: 8),
            Text(
              'Pantau saldo, hutang, dan riwayat pembayaran suplier.',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: AppColors.onSurfaceVariant),
            ),
            const SizedBox(height: 32),
            
            // Total Debt Summary
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: AppColors.onSurface,
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: AppColors.onSurface.withOpacity(0.2),
                    blurRadius: 20,
                    offset: const Offset(0, 10),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'TOTAL HUTANG SUPLIER',
                    style: GoogleFonts.inter(
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                      color: Colors.white.withOpacity(0.6),
                      letterSpacing: 1.5,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Rp 1.240.500.000',
                    style: GoogleFonts.manrope(
                      fontSize: 28,
                      fontWeight: FontWeight.w800,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(height: 24),
                  Row(
                    children: [
                      _buildMiniInfo(label: 'VENDOR AKTIF', value: '12'),
                      const SizedBox(width: 24),
                      _buildMiniInfo(label: 'JATUH TEMPO', value: '3'),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 32),
            
            Text(
              'Daftar Penjual Utama',
              style: GoogleFonts.manrope(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: AppColors.onSurface,
              ),
            ),
            const SizedBox(height: 16),
            
            // List of Suppliers (Mock)
            _buildSupplierItem(name: 'H. Syukri Kamal', region: 'Estate A', balance: 'Rp 45.2M'),
            _buildSupplierItem(name: 'PT. Sawit Jaya', region: 'Estate B', balance: 'Rp 128.0M'),
            _buildSupplierItem(name: 'Koperasi Mandiri', region: 'Estate A', balance: 'Rp 12.5M'),
          ],
        ),
      ),
    );
  }

  Widget _buildMiniInfo({required String label, required String value}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: GoogleFonts.inter(
            fontSize: 8,
            fontWeight: FontWeight.w600,
            color: Colors.white.withOpacity(0.4),
          ),
        ),
        Text(
          value,
          style: GoogleFonts.manrope(
            fontSize: 14,
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
      ],
    );
  }

  Widget _buildSupplierItem({required String name, required String region, required String balance}) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
      ),
      child: Row(
        children: [
           CircleAvatar(
            backgroundColor: AppColors.primaryContainer.withOpacity(0.1),
            child: Text(name[0], style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold)),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(name, style: GoogleFonts.manrope(fontWeight: FontWeight.bold)),
                Text(region, style: GoogleFonts.inter(fontSize: 11, color: AppColors.onSurfaceVariant)),
              ],
            ),
          ),
          Text(balance, style: GoogleFonts.manrope(fontWeight: FontWeight.w800, color: AppColors.secondary)),
        ],
      ),
    );
  }
}
