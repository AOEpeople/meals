import { useI18n } from 'vue-i18n';

export default function getDisplayName(fullname: string) {
    const { t } = useI18n();

    if (fullname.includes('(Guest)')) {
        return `${fullname.split(' (Guest)')[0]} (${t('menu.guest')})`;
    }
    return fullname;
}
