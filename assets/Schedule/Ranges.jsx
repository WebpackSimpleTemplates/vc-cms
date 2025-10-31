import { useEffect, useRef, useState } from 'react';

let elementX = null;
let activeElement = null;

/**
 * Это нужно для Next.js 13
 */
if (globalThis.document) {
  document.addEventListener('mouseup', () => {
    activeElement = null;
    elementX = 0;
  });

  document.addEventListener('mousemove', (e) => {
    if (
      e.clientX <= 0
      || e.clientX >= window.innerWidth
      || e.clientY <= 0
      || e.clientY >= window.innerHeight
    ) {
      activeElement = null;
      elementX = 0;
    }
  })
}

function Range({ values, setStart, setEnd }) {
  const startRef = useRef();
  const endRef = useRef();

  useEffect(() => {
    if (!globalThis.document) {
      return;
    }

    function moveStartPoint(ev) {
      if (activeElement !== startRef.current || !elementX) {
        return
      }

      const parentX = elementX;
      const pointX = ev instanceof TouchEvent ? ev.touches[0].clientX : ev.clientX;

      setStart(pointX - parentX);
    }

    document.addEventListener('mousemove', moveStartPoint);
    document.addEventListener('touchmove', moveStartPoint);

    return () => {
      document.removeEventListener('mousemove', moveStartPoint);
      document.removeEventListener('touchmove', moveStartPoint);
    }
  }, [setStart]);

  useEffect(() => {
    if (!globalThis.document) {
      return;
    }

    function moveEndPoint(ev) {
      if (activeElement !== endRef.current || !elementX) {
        return
      }

      const parentX = elementX;
      const pointX = ev instanceof TouchEvent ? ev.touches[0].clientX : ev.clientX;

      setEnd(pointX - parentX);
    }

    document.addEventListener('mousemove', moveEndPoint);
    document.addEventListener('touchmove', moveEndPoint);

    return () => {
      document.removeEventListener('mousemove', moveEndPoint);
      document.removeEventListener('touchmove', moveEndPoint);
    }
  }, [setEnd]);

  useEffect(() => {
    return () => {
      activeElement = null;
    }
  }, []);

  return (
    <div className="wui-ranges__range" style={{ left: values[0], width: values[1] - values[0] }}>
      <div
        className="wui-ranges__start"
        ref={startRef}
        onMouseDown={(e) => {
          activeElement = e.target;
        }}
      />
      <div
        className="wui-ranges__end"
        ref={endRef}
        onMouseDown={(e) => {
          activeElement = e.target;
        }}
      />
    </div>
  );
}

function RangesList({ values, setValues, maxValue = 100, color }) {
  const rootRef = useRef(null);

  const { width = 0 } = rootRef.current?.getBoundingClientRect() || {};

  const [,updateTrigger] = useState(0);

  useEffect(() => {
    setTimeout(() => { updateTrigger(t => t + 1) }, 100)
  }, [width]);

  const alignStart = ([startPx, end], index) => {
    const [,prevEnd = 0] = values[index - 1] || [];

    const prevPx = prevEnd * width / maxValue;
    const endPx = end  * width / maxValue;

    return Math.min(endPx, Math.max(startPx, prevPx)) * maxValue / width >> 0
  }

  function alignEnd([start, endPx], index) {
    const [nextStart = maxValue] = values[index + 1] || [];

    const startPx = start * width / maxValue;
    const nextPx = nextStart * width / maxValue;

    return Math.max(startPx, Math.min(endPx, nextPx)) * maxValue / width >> 0;
  }


  return (
    <div
      className={"wui-ranges" + (color ? ' wui-ranges_' + color : '')}
      ref={rootRef}
      onMouseDown={(e) => elementX = (e.currentTarget).getBoundingClientRect().left}
      onTouchStart={(e) => elementX = (e.currentTarget).getBoundingClientRect().left}
    >
      {values.map(([start, end], id) => (
        <Range
          key={id + 2}
          values={[start * width / maxValue, end * width / maxValue]}
          setStart={(start) => setValues(values.map((range, index) => id === index
            ? [alignStart([start, range[1]], index), range[1]]
            : range))
          }
          setEnd={(end) => setValues(values.map((range, index) => id === index
            ? [range[0], alignEnd([range[0], end], index)]
            : range))
          }
        />
      ))}
    </div>
  )
}

const stepMap = (val, step) => Math.round(val/step)*step;

export function Ranges({ values = [], setValues, max = 100, step = 1, color = 'default' }) {
  return (
    <RangesList
      color={color}
      maxValue={max}
      values={values}
      setValues={(valuesState) => {
        const mapped = valuesState.map(([start, end], id) => [
          Math.max(stepMap(start, step), 0),
          Math.min(stepMap(end, step), max),
        ]);

        const newValues = [];

        for (let index = 0; index < mapped.length; index++) {
          const current = mapped[index];
          const prev = mapped[index - 1];

          if (prev && prev[1] === current[0]) {
            prev[1] = current[1];
          } else {
            newValues.push(current);
          }
        }

        setValues(newValues);
      }}
    />
  )
}

export default Ranges;
