
@if (session('success'))
    <div
        data-popup-notification
        data-popup-type="success"
        data-popup-title="Berhasil!"
        data-popup-message="{{ session('success') }}"
        hidden
    ></div>
@endif

@if ($errors->has('delete'))
    <div
        data-popup-notification
        data-popup-type="error"
        data-popup-title="Gagal!"
        data-popup-message="{{ $errors->first('delete') }}"
        hidden
    ></div>
@endif
