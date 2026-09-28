@extends('layouts.customer')

@section('title', 'AI Makeup Recommendation | GlowAI')

@section('content')

<div class="ai-page">

    <div class="ai-heading">
        <h1>AI Makeup Recommendation</h1>
    </div>


    @if ($errors->any())
        <div class="ai-error-box">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif


    <div class="ai-upload-card">

        <div class="ai-card-heading">

            <h2>Find Your Perfect Makeup Look</h2>

            <p>
                Upload a clear photo and GlowAI will analyse your
                facial features and skin tone to recommend a makeup
                look suited to you.
            </p>

        </div>


        <form
            action="{{ route('customer.ai.upload') }}"
            method="POST"
            enctype="multipart/form-data"
            id="aiUploadForm"
        >
            @csrf
            <div class="ai-photo-options">
                 <button type="button" id="takePhotoBtn">
                    Take Photo
                </button>
            </div>

            <label for="face_image" class="ai-upload-box" id="aiUploadBox">

                {{-- Default upload content --}}
                <div class="ai-upload-placeholder" id="uploadPlaceholder">
                    <div class="upload-icon">+</div>

                    <strong>Choose Photo</strong>

                    <span>
                        JPG or PNG · Maximum 5 MB
                    </span>
                </div>

                {{-- Image preview --}}
                <div class="ai-image-preview" id="imagePreviewContainer">
                    <img
                        id="imagePreview"
                        src=""
                        alt="Selected photo preview"
                    >

                    <div class="ai-preview-overlay">
                        <span>Change Photo</span>
                    </div>
                </div>

                <input
                    type="file"
                    id="face_image"
                    name="face_image"
                    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                    required
                >

            </label>
            
            <div class="ai-camera-container" id="cameraContainer" style="display: none;">
                <video id="cameraPreview" autoplay playsinline></video>

                <div class="ai-camera-actions">
                    <button type="button" id="capturePhotoBtn">
                        Capture Photo
                    </button>

                    <button type="button" id="cancelCameraBtn">
                        Cancel
                    </button>
                </div>

                <canvas id="cameraCanvas" style="display: none;"></canvas>
            </div>

            {{-- Selected filename --}}
            <div class="ai-selected-file" id="selectedFile"></div>

            {{-- Client-side error --}}
            <div class="ai-file-error" id="fileError"></div>

            <button
                type="submit"
                class="ai-analyse-btn"
                id="analyseButton"
                disabled
            >
                Analyse My Photo
            </button>

        </form>


        <div class="ai-photo-tips">

            <div>
                <span>✓</span>
                Front-facing
            </div>

            <div>
                <span>✓</span>
                Natural lighting
            </div>

            <div>
                <span>✓</span>
                No filters
            </div>

            <div>
                <span>✓</span>
                No makeup
            </div>

        </div>

    </div>


    <div class="ai-privacy">


        <div>
            <strong>Your privacy matters</strong>

            <p>
                Your photo is used only for facial analysis and
                is not required to make a booking. Images are
                processed locally.
            </p>
        </div>

    </div>

</div>

@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {

    const input = document.getElementById('face_image');
    const placeholder = document.getElementById('uploadPlaceholder');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const preview = document.getElementById('imagePreview');
    const selectedFile = document.getElementById('selectedFile');
    const fileError = document.getElementById('fileError');
    const analyseButton = document.getElementById('analyseButton');
    const takePhotoBtn = document.getElementById('takePhotoBtn');
    const cameraContainer = document.getElementById('cameraContainer');
    const cameraPreview = document.getElementById('cameraPreview');
    const capturePhotoBtn = document.getElementById('capturePhotoBtn');
    const cancelCameraBtn = document.getElementById('cancelCameraBtn');
    const cameraCanvas = document.getElementById('cameraCanvas');

    
    let cameraStream = null;

            takePhotoBtn.addEventListener('click', async function () {
                fileError.textContent = '';

                try {
                    cameraStream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user'
                        },
                        audio: false
                    });

                    cameraPreview.srcObject = cameraStream;

                    document.getElementById('aiUploadBox').style.display = 'none';
                    cameraContainer.style.display = 'block';

                } catch (error) {
                    fileError.textContent =
                        'Camera could not be accessed. Please allow camera permission or upload a photo instead.';
                }
            });

            capturePhotoBtn.addEventListener('click', function () {
                const width = cameraPreview.videoWidth;
                const height = cameraPreview.videoHeight;

                if (!width || !height) {
                    fileError.textContent = 'Camera is not ready yet. Please try again.';
                    return;
                }

                cameraCanvas.width = width;
                cameraCanvas.height = height;

                const context = cameraCanvas.getContext('2d');

                context.drawImage(
                    cameraPreview,
                    0,
                    0,
                    width,
                    height
                );

                cameraCanvas.toBlob(function (blob) {
                    if (!blob) {
                        fileError.textContent = 'Photo could not be captured. Please try again.';
                        return;
                    }

                    const file = new File(
                        [blob],
                        'camera-photo.jpg',
                        { type: 'image/jpeg' }
                    );

                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input.files = dataTransfer.files;

                    stopCamera();

                    cameraContainer.style.display = 'none';
                    document.getElementById('aiUploadBox').style.display = '';

                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }, 'image/jpeg', 0.92);
            });

            cancelCameraBtn.addEventListener('click', function () {
                stopCamera();

                cameraContainer.style.display = 'none';
                document.getElementById('aiUploadBox').style.display = '';

                fileError.textContent = '';
            });

            function stopCamera() {
                if (cameraStream) {
                    cameraStream.getTracks().forEach(function (track) {
                        track.stop();
                    });

                    cameraStream = null;
                }

                cameraPreview.srcObject = null;
            }

        input.addEventListener('change', function () {

            const file = this.files[0];

            fileError.textContent = '';
            selectedFile.textContent = '';
            analyseButton.disabled = true;

            if (!file) {
                resetPreview();
                return;
            }

            const allowedTypes = ['image/jpeg', 'image/png'];

            if (!allowedTypes.includes(file.type)) {
                fileError.textContent = 'Please choose a JPG or PNG image.';
                input.value = '';
                resetPreview();
                return;
            }

            const maxSize = 5 * 1024 * 1024;

            if (file.size > maxSize) {
                fileError.textContent = 'Image must be 5 MB or smaller.';
                input.value = '';
                resetPreview();
                return;
            }

        const reader = new FileReader();

        reader.onload = function (event) {

            const testImage = new Image();

            testImage.onload = function () {

                const megapixels =
                    (testImage.naturalWidth * testImage.naturalHeight) / 1000000;

                if (megapixels > 20) {
                    fileError.textContent =
                        'Image must be under 20 megapixels.';

                    input.value = '';
                    resetPreview();
                    return;
                }

                preview.src = event.target.result;

                placeholder.style.display = 'none';
                previewContainer.style.display = 'block';

                selectedFile.textContent =
                    'Selected: ' + file.name;

                analyseButton.disabled = false;
            };

            testImage.onerror = function () {
                fileError.textContent =
                    'This image could not be previewed. Please choose another JPG or PNG image.';

                input.value = '';
                resetPreview();
            };

            testImage.src = event.target.result;
        };

        reader.onerror = function () {
            fileError.textContent =
                'This image could not be read. Please choose another image.';

            input.value = '';
            resetPreview();
        };

        reader.readAsDataURL(file);
        
    });
    
    function resetPreview() {
        preview.src = '';
        placeholder.style.display = 'flex';
        previewContainer.style.display = 'none';
        selectedFile.textContent = '';
        analyseButton.disabled = true;
    }

});
</script>