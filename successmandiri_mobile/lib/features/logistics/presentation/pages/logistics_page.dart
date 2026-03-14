import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:successmandiri_mobile/core/theme/app_theme.dart';

class LogisticsPage extends StatelessWidget {
  const LogisticsPage({super.key});

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
              'Logistics Management',
              style: Theme.of(context).textTheme.headlineSmall,
            ),
            const SizedBox(height: 4),
            Text(
              'Monitor armada kendaraan dan performa supir.',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: AppColors.onSurfaceVariant),
            ),
            const SizedBox(height: 32),
            
            // Logistics Overview Cards
            Row(
              children: [
                _buildLogisticsCard(
                  context,
                  label: 'SUPIR AKTIF',
                  value: '18',
                  icon: Icons.person_pin_circle_rounded,
                ),
                const SizedBox(width: 16),
                _buildLogisticsCard(
                  context,
                  label: 'ARMADA',
                  value: '24',
                  icon: Icons.local_shipping_rounded,
                ),
              ],
            ),
            const SizedBox(height: 32),
            
            _buildSectionHeader('Daftar Supir & Armada'),
            const SizedBox(height: 16),
            
            _buildLogisticsItem(
              name: 'Ahmad Subarjo',
              plate: 'BM 8821 TU',
              status: 'READY',
              statusColor: Colors.green,
            ),
            _buildLogisticsItem(
              name: 'Budi Santoso',
              plate: 'BM 1245 AV',
              status: 'ON DELIVERY',
              statusColor: AppColors.secondary,
            ),
             _buildLogisticsItem(
              name: 'Indra Wijaya',
              plate: 'BM 9920 ZL',
              status: 'MAINTENANCE',
              statusColor: AppColors.error,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          title,
          style: GoogleFonts.manrope(
            fontSize: 16,
            fontWeight: FontWeight.bold,
          ),
        ),
        Text(
          'LIHAT SEMUA',
          style: GoogleFonts.inter(
            fontSize: 10,
            fontWeight: FontWeight.bold,
            color: AppColors.primary,
            letterSpacing: 0.5,
          ),
        ),
      ],
    );
  }

  Widget _buildLogisticsCard(BuildContext context, {required String label, required String value, required IconData icon}) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, color: AppColors.onSurfaceVariant.withOpacity(0.4), size: 24),
            const SizedBox(height: 16),
            Text(
              label,
              style: GoogleFonts.inter(
                fontSize: 9,
                fontWeight: FontWeight.bold,
                color: AppColors.onSurfaceVariant.withOpacity(0.6),
                letterSpacing: 1,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              value,
              style: GoogleFonts.manrope(
                fontSize: 22,
                fontWeight: FontWeight.w800,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLogisticsItem({required String name, required String plate, required String status, required Color statusColor}) {
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
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: AppColors.background,
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.drive_eta_rounded, color: AppColors.onSurfaceVariant, size: 20),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(name, style: GoogleFonts.manrope(fontWeight: FontWeight.bold, fontSize: 14)),
                Text(plate, style: GoogleFonts.inter(fontSize: 11, color: AppColors.onSurfaceVariant, fontWeight: FontWeight.w500)),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: statusColor.withOpacity(0.1),
              borderRadius: BorderRadius.circular(6),
            ),
            child: Text(
              status,
              style: GoogleFonts.inter(
                fontSize: 9,
                fontWeight: FontWeight.bold,
                color: statusColor,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
