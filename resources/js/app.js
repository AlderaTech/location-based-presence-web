import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

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
