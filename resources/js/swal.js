import Swal from 'sweetalert2';

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

export function swalSuccess(message = 'Berhasil!') {
    return Toast.fire({ icon: 'success', title: message });
}

export function swalError(message = 'Terjadi kesalahan') {
    return Swal.fire({ icon: 'error', title: 'Gagal', text: message, confirmButtonColor: '#dc2626' });
}

export function swalWarning(message = 'Perhatian') {
    return Swal.fire({ icon: 'warning', title: 'Perhatian', text: message, confirmButtonColor: '#f59e0b' });
}

export async function swalConfirm(message = 'Yakin ingin menghapus?', confirmText = 'Ya, Hapus') {
    const result = await Swal.fire({
        title: 'Konfirmasi',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: confirmText,
        cancelButtonText: 'Batal',
    });
    return result.isConfirmed;
}

export function swalLoading(message = 'Menyimpan...') {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });
}

export function swalClose() {
    Swal.close();
}

export default Swal;
