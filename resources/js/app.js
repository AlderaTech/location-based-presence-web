import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Filler,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';
import {
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
    CalendarClock,
    CalendarDays,
    CalendarRange,
    ChartColumnBig,
    Check,
    Circle,
    CircleCheckBig,
    CircleX,
    ClipboardCheck,
    Clock3,
    createIcons,
    FileCheck2,
    FileText,
    Filter,
    Inbox,
    LayoutDashboard,
    LogOut,
    MapPin,
    Paperclip,
    Search,
    ShieldCheck,
    Stethoscope,
    TriangleAlert,
    UsersRound,
    X,
} from 'lucide';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

Chart.register(
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    DoughnutController,
    Filler,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
);

const lucideIcons = {
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
    CalendarClock,
    CalendarDays,
    CalendarRange,
    ChartColumnBig,
    Check,
    Circle,
    CircleCheckBig,
    CircleX,
    ClipboardCheck,
    Clock3,
    FileCheck2,
    FileText,
    Filter,
    Inbox,
    LayoutDashboard,
    LogOut,
    MapPin,
    Paperclip,
    Search,
    ShieldCheck,
    Stethoscope,
    TriangleAlert,
    UsersRound,
    X,
};

const toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3600,
    timerProgressBar: true,
    customClass: {
        popup: 'rounded-md border border-slate-200 shadow-lg',
        title: 'text-sm font-medium',
    },
});

const fireToast = (type, message) => {
    if (!message) {
        return;
    }

    toast.fire({
        icon: type,
        title: message,
    });
};

const notification = window.presensiNotifikasi || {};

fireToast('success', notification.sukses);
fireToast('error', notification.galat);

window.presensiToast = (message, type = 'success') => fireToast(type, message);

document.addEventListener('presensi:toast', (event) => {
    fireToast(event.detail?.type || 'success', event.detail?.message);
});

const chartTextColor = '#475569';
const chartGridColor = '#E2E8F0';

const baseChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                color: chartTextColor,
                boxWidth: 12,
                boxHeight: 12,
                usePointStyle: true,
            },
        },
        tooltip: {
            backgroundColor: '#0F172A',
            padding: 12,
            titleColor: '#FFFFFF',
            bodyColor: '#E2E8F0',
            displayColors: true,
        },
    },
};

const initDashboardCharts = () => {
    const data = window.presensiDashboard;

    if (!data) {
        return;
    }

    const trendCanvas = document.getElementById('grafikTrenPresensi');
    const statusCanvas = document.getElementById('grafikStatusBulanan');
    const leaveCanvas = document.getElementById('grafikPengajuanIzin');

    if (trendCanvas) {
        new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: data.trenHarian.labels,
                datasets: [
                    {
                        label: 'Tepat Waktu',
                        data: data.trenHarian.hadir,
                        borderColor: '#0F766E',
                        backgroundColor: 'rgba(15, 118, 110, 0.12)',
                        tension: 0.35,
                        fill: true,
                    },
                    {
                        label: 'Terlambat',
                        data: data.trenHarian.terlambat,
                        borderColor: '#D97706',
                        backgroundColor: 'rgba(217, 119, 6, 0.10)',
                        tension: 0.35,
                        fill: true,
                    },
                    {
                        label: 'Izin/Sakit/Cuti',
                        data: data.trenHarian.izin,
                        borderColor: '#2563EB',
                        backgroundColor: 'rgba(37, 99, 235, 0.08)',
                        tension: 0.35,
                        fill: true,
                    },
                ],
            },
            options: {
                ...baseChartOptions,
                scales: {
                    x: {
                        ticks: { color: chartTextColor, maxRotation: 0 },
                        grid: { display: false },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { color: chartTextColor, precision: 0 },
                        grid: { color: chartGridColor },
                    },
                },
                elements: {
                    point: { radius: 2.5, hoverRadius: 5 },
                    line: { borderWidth: 2 },
                },
            },
        });
    }

    if (statusCanvas) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: data.statusBulanan.labels,
                datasets: [
                    {
                        data: data.statusBulanan.data,
                        backgroundColor: data.statusBulanan.colors,
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                ...baseChartOptions,
                cutout: '64%',
            },
        });
    }

    if (leaveCanvas) {
        new Chart(leaveCanvas, {
            type: 'bar',
            data: {
                labels: data.pengajuan.labels,
                datasets: [
                    {
                        label: 'Pengajuan',
                        data: data.pengajuan.data,
                        backgroundColor: ['#D97706', '#0F766E', '#DC2626'],
                        borderRadius: 6,
                        maxBarThickness: 42,
                    },
                ],
            },
            options: {
                ...baseChartOptions,
                plugins: {
                    ...baseChartOptions.plugins,
                    legend: { display: false },
                },
                scales: {
                    x: {
                        ticks: { color: chartTextColor },
                        grid: { display: false },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { color: chartTextColor, precision: 0 },
                        grid: { color: chartGridColor },
                    },
                },
            },
        });
    }
};

const confirmConfig = (form) => {
    const variant = form.dataset.confirmVariant || 'warning';
    const confirmButtonColor = {
        danger: '#DC2626',
        warning: '#D97706',
        success: '#0F766E',
        info: '#2563EB',
    }[variant] || '#0F172A';

    return {
        title: form.dataset.confirmTitle || 'Konfirmasi tindakan',
        text: form.dataset.confirmText || 'Tindakan ini akan langsung diproses.',
        icon: variant === 'danger' ? 'warning' : variant,
        confirmButtonText: form.dataset.confirmButton || 'Lanjutkan',
        cancelButtonText: form.dataset.cancelButton || 'Batal',
        reverseButtons: true,
        focusCancel: true,
        showCancelButton: true,
        confirmButtonColor,
        cancelButtonColor: '#64748B',
        customClass: {
            popup: 'rounded-md',
            confirmButton: 'text-sm font-semibold',
            cancelButton: 'text-sm font-semibold',
        },
    };
};

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !form.matches('[data-confirm]')) {
        return;
    }

    if (form.dataset.confirmed === 'true') {
        return;
    }

    if (!form.checkValidity()) {
        return;
    }

    event.preventDefault();

    Swal.fire(confirmConfig(form)).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        form.dataset.confirmed = 'true';
        form.requestSubmit(event.submitter || undefined);
    });
});

document.addEventListener('DOMContentLoaded', () => {
    createIcons({
        icons: lucideIcons,
        attrs: {
            width: 18,
            height: 18,
            'stroke-width': 1.8,
        },
    });
    initDashboardCharts();
});
