import { useParticipationsListData } from '@/api/getParticipationsByDay';
import type { IProfile } from '@/stores/profilesStore';
import { type Ref, computed, reactive } from 'vue';

interface ParticipantState {
    participants: Readonly<Ref<readonly IProfile[]>>;
    filterValue: string;
    isLoading: boolean;
    error: string;
}

export function filterParticipantsList(date: string) {
    const { listData } = useParticipationsListData(date);

    const participations = reactive<ParticipantState>({
        participants: listData as Ref<readonly IProfile[]>,
        filterValue: '',
        isLoading: false,
        error: ''
    });

    function setFilter(filterStr: string) {
        participations.filterValue = filterStr;
    }

    const filteredParticipants = computed(() => {
        return participations.participants.filter((participant) =>
            participantsContainString(participant, participations.filterValue)
        );
    });

    function participantsContainString(participant: IProfile, filterInput: string) {
        return participant.fullName.toLowerCase().includes(filterInput.toLowerCase());
    }

    return {
        filteredParticipants,
        setFilter
    };
}
