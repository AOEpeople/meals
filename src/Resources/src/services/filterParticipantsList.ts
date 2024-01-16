import { useParticipationsListData } from "@/api/getParticipationsByDay";
import { Ref, computed, reactive } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

interface ParticipantState {
  participants: Readonly<Ref<readonly string[]>>,
  filterValue: string,
  isLoading: boolean,
  error: string
}

export function filterParticipantsList(date: string){

  const { listData } = useParticipationsListData(date);
  const participations  = reactive<ParticipantState>({
    participants: listData,
    filterValue: '',
    isLoading: false,
    error: ''
  });

  function setFilter(filterStr: string) {
    participations.filterValue = filterStr;
  }

  const filteredParticipants = computed(() => {
    if (participations.participants.length===0){
      return ["Heute gibt es keine Teilnehmer."]
    }
    return participations.participants.filter(participant => participantsContainString(participant, participations.filterValue));
  });

  function participantsContainString(participant: string, filterInput: string) {
    return (
      participant.toLowerCase().includes(filterInput.toLowerCase())
    );
  }

  return {
      filteredParticipants,
      setFilter
  };
}
