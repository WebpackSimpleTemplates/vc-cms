import Ranges from "./Ranges";

function toTime(minutes) {
  return (((minutes / 60 >> 0) > 9 ? '' : '0') + (minutes / 60 >> 0) + ':' + ((minutes % 60 > 9 >> 0) ? '' : '0') + (minutes % 60 >> 0))
    .split('')
    .map((symbol, id) => <span key={symbol + ':' + id}>{symbol}</span>);
}

export function ScheduleRanges({ dayWeek, values = [], setValue = () => {}, color = 'default' }) {

  const addRange = () => {
    const step = 1440 / ((values.length + 1) * 2);

    setValue(
      new Array(Math.min(values.length + 1, 8))
        .fill(null)
        .map((_, index) => [index*2*step, index*2*step + step])
    )
  };

  function copy() {
    function joinRange(range) {
      return range.join('\t')
    }

    const value = values.map(joinRange).join('\n');

    navigator.clipboard.writeText(value);
  }

  async function paste() {
    const clipboardValue = await navigator.clipboard.readText();

    function splitRange(str) {
      return str.split('\t').map((num) => +num).filter((num) => !isNaN(num));
    }

    setValue(clipboardValue.split('\n').map(splitRange))
  }

  return (
    <div className="wui-schedule-ranges">
      <div className="wui-schedule-ranges__header">
        <div className="wui-schedule-ranges__name">{dayWeek}</div>
        <div className="wui-schedule-ranges__btns">
          <button type="button" className="btn" disabled={values.length >= 8} onClick={addRange}>
            Добавить
          </button>
          <button type="button" className="btn" onClick={copy}>
            Копировать
          </button>
          <button type="button" className="btn" onClick={paste}>
            Вставить
          </button>
        </div>
      </div>
      <div className="wui-schedule-ranges__line">
        00:00
        <Ranges
          color={color}
          values={values}
          setValues={setValue}
          max={1440}
          step={5}
        />
        24:00
      </div>
      <div className="wui-schedule-ranges__times">
        {values.map(([start, end]) => (
          <div key={start + ':' + end} className="wui-schedule-ranges__times-pair">
            <div className="wui-schedule-ranges__time">
              {toTime(start)}
            </div>
            <div className="wui-schedule-ranges__separate" />
            <div className="wui-schedule-ranges__time">
              {toTime(end)}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
