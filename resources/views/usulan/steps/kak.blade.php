@php
    $bantuan = [
        'dasar_hukum' => 'Peraturan yang mendasari kegiatan. Satu peraturan per baris.',
        'latar_belakang' => 'Mengapa kegiatan ini perlu dilaksanakan.',
        'tujuan' => 'Apa yang ingin dicapai.',
        'penerima_manfaat' => 'Siapa yang mendapat manfaat dan berapa jumlahnya.',
        'metode_pelaksanaan' => 'Bagaimana kegiatan dilaksanakan (swakelola, kontraktual, tahapan).',
        'uraian_kebutuhan' => 'Ringkasan kebutuhan biaya yang akan dirinci di RAB.',
    ];
@endphp

<x-usulan.wizard :usulan="$usulan" :step="$step" :steps="$steps" :kelengkapan="$kelengkapan">
    <form method="POST" action="{{ route('usulan.kak', $usulan) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <x-ui.card title="Kerangka Acuan Kerja (KAK/TOR)" description="Semua bagian wajib diisi sebelum usulan dikirim.">
            <div class="space-y-5">
                @foreach (\App\Models\Usulan::WAJIB_KAK as $field => $label)
                    <x-ui.field :label="$label" :name="$field" required :hint="$bantuan[$field]">
                        <x-ui.textarea :name="$field" :value="$usulan->{$field}" rows="4" />
                    </x-ui.field>
                @endforeach
            </div>
        </x-ui.card>

        <x-usulan.step-actions :usulan="$usulan" prev="info" />
    </form>
</x-usulan.wizard>
