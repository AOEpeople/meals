export default function getDisplayName(fullname: string, t: (str: string) => string ) {
    if (fullname.includes('(Guest)')) {
        return `${fullname.split(' (Guest)')[0]} (${t('menu.guest')})`;
    }
    return fullname;
}
