class FileHandler {
    constructor() {
        this.baseUrl = this.getBaseUrl();
    }

   getBaseUrl() {
        const { origin, hostname, pathname } = new URL(window.location.href);
        const pathSegments = pathname.split('/').filter(Boolean);
    
        if (hostname === 'localhost') {
            const projectName = pathSegments[0] || '';
            return projectName ? `${origin}/${projectName}` : origin;
        }
    
        if (/^(?:\d{1,3}\.){3}\d{1,3}$/.test(hostname)) {
            return origin; // Hostname adalah IP address
        }
    
        if (hostname.split('.').length > 2) {
            return origin; // Hostname memiliki subdomain
        }
    
        return origin; // Default: kembalikan origin
    }


    loadFile(id, path, file, role) {
        if (!file) {
            document.getElementById(id).innerText = "No file specified.";
            return;
        }

        const extension = file.split('.').pop().toLowerCase();
        const imageUrl = `${this.baseUrl}/${role}/preview/${encodeURIComponent(path)}/${encodeURIComponent(file)}`;
        const container = document.getElementById(id);

        container.innerHTML = this.getPreviewHtml(extension, imageUrl);
    }

    getPreviewHtml(extension, imageUrl) {
        if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension)) {
            return `<img src="${imageUrl}" class="img-fluid mt-5 h-lg-250px h-md-150px" alt="Image" onclick="fileHandler.showImageModal(this)" loading="lazy">`;
        } else if (extension === 'pdf') {
            return `<a href="${imageUrl}" target="_blank" class="btn btn-sm btn-success mt-5 w-100 mb-8">Preview PDF</a>`;
        }
        return "This file type is not supported.";
    }

    validateAndPreview(input, containerId) {
        const file = input.files[0];
        if (!file) {
            alert('No file selected.');
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp', 'image/webp', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            alert('Only specific image types are allowed.');
            input.value = '';
            return;
        }

        const maxSizeBytes = 2 * 1024 * 1024; // 2 MB
        if (file.size > maxSizeBytes) {
            alert('The file is too large.');
            input.value = '';
            return;
        }

        this.previewFile(file, containerId);
    }

    previewFile(file, containerId) {
        const extension = file.type.split('/').pop().toLowerCase();
        const container = document.getElementById(containerId);
        container.innerHTML = ''; // Clear previous content

        if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension)) {
            this.appendImagePreview(file, container);
        } else if (extension === 'pdf') {
            this.appendPdfPreview(file, container);
        } else {
            container.innerText = 'This file type is not supported for preview.';
        }
    }

    appendImagePreview(file, container) {
        const img = new Image();
        img.classList.add('img-fluid', 'mt-5', 'h-lg-250px', 'h-md-150px');
        img.alt = "Image Preview";
        img.onload = () => $("#myModal").modal("show");
        img.src = URL.createObjectURL(file);
        container.appendChild(img);
    }

    appendPdfPreview(file, container) {
        const previewButton = document.createElement('a');
        previewButton.href = URL.createObjectURL(file);
        previewButton.target = '_blank';
        previewButton.classList.add('btn', 'btn-primary', 'btn-sm', 'mt-5', 'w-100', 'mb-8');
        previewButton.innerText = 'Preview PDF';
        container.appendChild(previewButton);
    }

    showImageModal(img) {
        $("#myModal").modal("show");
        $(".modal-img").prop("src", img.src);
    }
}

const fileHandler = new FileHandler();
