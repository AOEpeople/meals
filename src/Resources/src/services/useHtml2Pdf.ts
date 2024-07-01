import { jsPDF } from 'jspdf';
import domtoimage from 'dom-to-image';
import useFlashMessage from '@/services/useFlashMessage';
import { FlashMessageType } from '@/enums/FlashMessage';
import { useI18n } from 'vue-i18n';

export default function useHtml2Pdf() {
    const { t } = useI18n();

    async function generatePdf(content: HTMLDivElement, filename: string = 'result') {
        try {
            const imageUrl = await domtoimage.toPng(content, {});

            const img = new Image();
            img.src = imageUrl;
            img.onload = () => {
                const imgWidth = img.width;
                const imgHeight = img.height;
                const a4Ratio = 0.7070757462; // Ratio of width/height for an A4-Paper

                const pageWidth = imgWidth;
                const pageHeight = imgWidth / a4Ratio;
                const pdf = new jsPDF('p', 'px', [pageWidth, pageHeight]);

                for (let y = 0; y < imgHeight; y += pageHeight) {
                    pdf.addImage(imageUrl, 'PNG', 0, -y, pageWidth, imgHeight);
                    if (y + pageHeight < imgHeight) {
                        pdf.addPage([pageWidth, pageHeight], 'p');
                    }
                }
                pdf.save(`${filename}.pdf`);
            };
        } catch (error) {
            console.error('An error occured during pdf generation!', error);
            useFlashMessage().sendFlashMessage({
                type: FlashMessageType.ERROR,
                message: t('flashmessage.print.error')
            });
        }
    }

    return {
        generatePdf
    };
}
