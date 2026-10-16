window.mediaLibraryUpload = function (wire) {
    return {
        busy: false, saving: false, progress: 0, dragging: false, error: '', status: '', names: [],
        receive(fileList) {
            if (this.busy) return;
            const files = Array.from(fileList || []);
            if (!files.length) return;
            this.error = '';
            this.status = '';
            if (files.length > 20 || files.some(file => file.size > 10 * 1024 * 1024)) {
                this.error = 'До 20 файлов, до 10 МБ каждый.';
                return;
            }
            const folder = wire.folder;
            this.names = files.map(file => file.name);
            this.progress = 0;
            this.busy = true;
            const failed = () => {
                this.busy = false;
                this.saving = false;
                this.error = 'Не удалось передать файлы. Повторите загрузку.';
            };
            try {
                wire.uploadMultiple('uploads', files, async () => {
                    this.progress = 100;
                    this.saving = true;
                    this.status = 'Сохранение файлов…';
                    try {
                        const saved = await wire.uploadFiles(folder);
                        this.status = saved === true ? 'Загрузка завершена.' : '';
                        if (saved !== true) this.error = 'Файлы не сохранены. Проверьте сообщения об ошибках.';
                    } catch (error) {
                        this.status = '';
                        this.error = 'Не удалось сохранить файлы. Проверьте папку назначения и повторите загрузку.';
                    } finally {
                        this.busy = false;
                        this.saving = false;
                    }
                }, failed, event => {
                    this.progress = Math.max(0, Math.min(100, Math.round(event.detail.progress)));
                }, () => {
                    this.busy = false;
                    this.progress = 0;
                    this.status = 'Передача отменена.';
                }, false);
            } catch (error) { failed(); }
        },
        cancel() {
            if (this.busy && !this.saving) wire.cancelUpload('uploads');
        },
        drop(event) {
            this.dragging = false;
            this.receive(event.dataTransfer.files);
        },
    };
};
