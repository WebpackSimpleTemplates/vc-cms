import { useState } from 'react';
import { ScheduleRanges } from './ScheduleRange';
import { createRoot } from 'react-dom/client';

const input = document.querySelector('input[name="schedule[times]"]');
const container = document.getElementById("times");

function App() {
    const [state, setState] = useState(() => JSON.parse(input.value));

    const setByIndex = (index, value) => {
        const newState = [...state];

        newState[index] = value;

        setState(newState);

        input.value = JSON.stringify(newState);
    }

    return (
        <div className='flex flex-col gap-2'>
            <ScheduleRanges
                dayWeek="Понедельник"
                values={state[0]}
                setValue={(value) => setByIndex(0, value)}
            />
            <ScheduleRanges
                dayWeek="Вторник"
                values={state[1]}
                setValue={(value) => setByIndex(1, value)}
            />
            <ScheduleRanges
                dayWeek="Среда"
                values={state[2]}
                setValue={(value) => setByIndex(2, value)}
            />
            <ScheduleRanges
                dayWeek="Четверг"
                values={state[3]}
                setValue={(value) => setByIndex(3, value)}
            />
            <ScheduleRanges
                dayWeek="Пятница"
                values={state[4]}
                setValue={(value) => setByIndex(4, value)}
            />
            <ScheduleRanges
                dayWeek="Суббота"
                values={state[5]}
                setValue={(value) => setByIndex(5, value)}
            />
            <ScheduleRanges
                dayWeek="Воскресенье"
                values={state[6]}
                setValue={(value) => setByIndex(6, value)}
            />
        </div>
    );
}

createRoot(container).render(<App />)
