<script src="https://cdnjs.cloudflare.com/ajax/libs/compressorjs/1.2.1/compressor.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInputs = document.querySelectorAll('input[type="file"][name="photo"], input[type="file"][name="id_proof"]');

        fileInputs.forEach(input => {
            input.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (!file) return;

                // 200KB limit
                const maxSize = 200 * 1024;

                if (file.size > maxSize) {
                    if (confirm(`The file "${file.name}" is larger than 200KB. Would you like to automatically compress and resize it?`)) {

                        // Disable the submit button to prevent upload while compressing
                        const form = input.closest('form');
                        const submitBtns = form ? form.querySelectorAll('button[type="submit"]') : [];
                        submitBtns.forEach(btn => btn.disabled = true);

                        new Compressor(file, {
                            quality: 0.6,
                            maxWidth: 1000,
                            maxHeight: 1000,
                            success(result) {
                                const compressedFile = new File([result], file.name, {
                                    type: result.type,
                                    lastModified: Date.now(),
                                });

                                const dataTransfer = new DataTransfer();
                                dataTransfer.items.add(compressedFile);
                                input.files = dataTransfer.files;

                                if (compressedFile.size > maxSize) {
                                    alert("Even after compression, the file is larger than 200KB. Please select a smaller file.");
                                    input.value = '';
                                } else {
                                    alert("File successfully resized to " + (compressedFile.size / 1024).toFixed(2) + " KB!");
                                }

                                submitBtns.forEach(btn => btn.disabled = false);
                            },
                            error(err) {
                                console.error(err.message);
                                alert("Error resizing file. Please choose a smaller file.");
                                input.value = '';
                                submitBtns.forEach(btn => btn.disabled = false);
                            },
                        });
                    } else {
                        input.value = '';
                    }
                }
            });
        });
    });
</script>